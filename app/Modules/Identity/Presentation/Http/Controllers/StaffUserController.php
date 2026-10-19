<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Geography\Domain\Models\District;
use App\Modules\Geography\Domain\Models\Region;
use App\Modules\Identity\Application\Access\InviteStaff;
use App\Modules\Identity\Application\Access\ManageAccess;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffUserController
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));

        return view('admin.users.index', [
            'users' => $this->directory($term)->paginate(12)->withQueryString(),
            'roles' => Role::query()->where('name', '!=', AccessRoles::INVESTOR)->orderBy('name')->get(),
            'regions' => Region::query()->with(['districts' => fn ($query) => $query->orderBy('name')])->orderBy('name')->get(),
            'term' => $term,
        ]);
    }

    public function invite(Request $request, InviteStaff $invite): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{6,14}$/', 'unique:users,phone'],
            'role' => ['required', 'uuid'],
            'districts' => ['array'],
            'districts.*' => ['uuid'],
        ]);

        $invite->invite($request->user(), $data);

        return redirect()->route('admin.users.index')->with('status', 'Invitation sent. The staff member sets a password from that e-mail.');
    }

    public function export(Request $request): StreamedResponse
    {
        $format = $request->query('format', 'csv');
        $rows = $this->directory(trim((string) $request->query('q', '')))->get();
        $header = ['Name', 'Email', 'Account', 'Roles', 'Districts'];
        $lines = $rows->map(fn (User $user): array => [
            $user->name,
            $user->email,
            $user->isStaff() ? 'Staff' : 'Investor',
            $user->roles->pluck('name')->join(', '),
            $user->districts->pluck('code')->join(', '),
        ])->all();

        return match ($format) {
            'pdf' => response()->streamDownload(function () use ($header, $lines): void {
                echo $this->pdf($header, $lines);
            }, 'iomp-users.pdf', ['Content-Type' => 'application/pdf']),
            'xls' => response()->streamDownload(function () use ($header, $lines): void {
                echo $this->spreadsheet($header, $lines);
            }, 'iomp-users.xls', ['Content-Type' => 'application/vnd.ms-excel']),
            default => response()->streamDownload(function () use ($header, $lines): void {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $header);
                foreach ($lines as $line) {
                    fputcsv($handle, $line);
                }
                fclose($handle);
            }, 'iomp-users.csv', ['Content-Type' => 'text/csv']),
        };
    }

    public function edit(User $user): View
    {
        abort_unless($user->isStaff(), 404);

        return view('admin.users.edit', [
            'account' => $user->load(['roles', 'districts.region']),
            'roles' => Role::query()->where('name', '!=', AccessRoles::INVESTOR)->orderBy('name')->get(),
            'selected' => $user->roles->pluck('uuid')->all(),
            'regions' => Region::query()->with(['districts' => fn ($query) => $query->orderBy('name')])->orderBy('name')->get(),
            'selectedDistricts' => $user->districts->pluck('uuid')->all(),
        ]);
    }

    public function update(Request $request, User $user, ManageAccess $access, AuditRecorder $audit): RedirectResponse
    {
        abort_unless($user->isStaff(), 404);
        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['uuid'],
            'districts' => ['array'],
            'districts.*' => ['uuid'],
        ]);

        /** @var User $actor */
        $actor = $request->user();

        DB::transaction(function () use ($access, $audit, $actor, $user, $data): void {
            $access->assignRoles($actor, $user, array_values($data['roles'] ?? []));

            $districts = District::query()->whereIn('uuid', $data['districts'] ?? [])->get();
            $user->districts()->sync($districts->modelKeys());

            $audit->record(
                AuditEntry::for(IdentityAuditActions::STAFF_DISTRICTS_ASSIGNED, $user)
                    ->causedBy($actor)
                    ->withMetadata(['districts' => $districts->pluck('code')->all()])
            );
        });

        return redirect()->route('admin.users.edit', $user)->with('status', 'The account, role and district assignment were saved.');
    }

    /**
     * @return Builder<User>
     */
    private function directory(string $term): Builder
    {
        return User::query()
            ->with(['roles', 'districts'])
            ->when($term !== '', function (Builder $query) use ($term): void {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(function (Builder $inner) use ($like): void {
                    $inner->where('name', 'ilike', $like)->orWhere('email', 'ilike', $like);
                });
            })
            ->orderBy('name');
    }

    /**
     * @param  list<string>  $header
     * @param  list<list<string>>  $lines
     */
    private function spreadsheet(array $header, array $lines): string
    {
        $xml = '<?xml version="1.0"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Users" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Table>';
        $xml .= '<Row>'.implode('', array_map(fn (string $cell): string => '<Cell><Data ss:Type="String">'.$this->xml($cell).'</Data></Cell>', $header)).'</Row>';

        foreach ($lines as $line) {
            $xml .= '<Row>'.implode('', array_map(fn (string $cell): string => '<Cell><Data ss:Type="String">'.$this->xml($cell).'</Data></Cell>', $line)).'</Row>';
        }

        return $xml.'</Table></Worksheet></Workbook>';
    }

    /**
     * @param  list<string>  $header
     * @param  list<list<string>>  $lines
     */
    private function pdf(array $header, array $lines): string
    {
        $text = implode(' | ', $header);
        foreach ($lines as $line) {
            $text .= "\n".implode(' | ', $line);
        }

        $safe = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $text);
        $stream = "BT /F1 9 Tf 40 800 Td 12 TL (".$safe.") Tj ET";
        $objects = [
            "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n",
            "2 0 obj<< /Type /Pages /Count 1 /Kids [3 0 R] >>endobj\n",
            "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Contents 4 0 R /Resources<< /Font<< /F1 5 0 R >> >> >>endobj\n",
            '4 0 obj<< /Length '.strlen($stream)." >>stream\n".$stream."\nendstream\nendobj\n",
            "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1);
    }
}
