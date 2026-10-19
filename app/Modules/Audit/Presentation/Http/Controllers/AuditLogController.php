<?php

namespace App\Modules\Audit\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Audit\Infrastructure\Persistence\AuditRecord;
use App\Modules\Shared\Infrastructure\Export\SpreadsheetExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController
{
    public function index(Request $request): View
    {
        return view('admin.audit.index', [
            'records' => $this->filtered($request)->paginate(20)->withQueryString(),
            'actions' => AuditRecord::query()->select('event')->whereNotNull('event')->distinct()->orderBy('event')->limit(80)->pluck('event'),
        ]);
    }

    public function export(Request $request, AuditRecorder $audit): StreamedResponse
    {
        $format = $request->query('format') === 'xls' ? 'xls' : 'csv';
        $rows = $this->filtered($request)->limit(2000)->get();

        $audit->record(
            AuditEntry::for('audit.exported')
                ->causedBy($request->user())
                ->withMetadata(['format' => $format, 'rows' => $rows->count()]),
        );

        $header = ['Recorded', 'Action', 'Actor', 'Source', 'IP address', 'Request'];
        $lines = $rows->map(fn (AuditRecord $record): array => [
            optional($record->created_at)->toDateTimeString() ?? '',
            (string) ($record->event ?: $record->description),
            (string) ($record->causer?->name ?? ''),
            (string) $record->source,
            (string) $record->ip_address,
            (string) $record->request_id,
        ])->all();

        return $format === 'xls'
            ? SpreadsheetExport::excel('iomp-audit.xls', 'Audit', $header, $lines)
            : SpreadsheetExport::csv('iomp-audit.csv', $header, $lines);
    }

    /**
     * @return Builder<AuditRecord>
     */
    private function filtered(Request $request): Builder
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $action = trim((string) $request->query('action', ''));
        $term = trim((string) $request->query('q', ''));

        return AuditRecord::query()
            ->with('causer')
            ->when($action !== '', fn (Builder $query) => $query->where('event', $action))
            ->when($term !== '', function (Builder $query) use ($term): void {
                $like = '%'.addcslashes(mb_substr($term, 0, 80), '%_\\').'%';
                $query->where(function (Builder $inner) use ($like): void {
                    $inner->where('description', 'ilike', $like)->orWhere('event', 'ilike', $like);
                });
            })
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->query('to')))
            ->orderByDesc('id');
    }
}
