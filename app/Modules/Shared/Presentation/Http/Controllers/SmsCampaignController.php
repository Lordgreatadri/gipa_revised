<?php

namespace App\Modules\Shared\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Shared\Infrastructure\Sms\CampaignAudience;
use App\Modules\Shared\Infrastructure\Sms\SendSmsCampaign;
use App\Modules\Shared\Infrastructure\Sms\SmsCampaign;
use App\Modules\Shared\Infrastructure\Sms\SmsCampaignRecipient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class SmsCampaignController
{
    public function index(): View
    {
        return view('admin.sms.campaigns', [
            'campaigns' => SmsCampaign::query()->latest('id')->paginate(12),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.sms.campaign-create', [
            'canMessageStaff' => $request->user()->can(AccessPermissions::SMS_MANAGE),
        ]);
    }

    public function store(Request $request, CampaignAudience $audience, AuditRecorder $audit): RedirectResponse
    {
        $batch = (string) $request->input('batch');

        if ($batch === 'numbers') {
            $request->merge(['audience' => 'numbers']);
        }

        if ($batch === 'file') {
            $request->merge(['audience' => 'file']);
        }

        $data = $request->validate([
            'batch' => ['required', Rule::in(['directory', 'numbers', 'file'])],
            'name' => ['required', 'string', 'max:120'],
            'audience' => ['required', Rule::in(['active_investors', 'all_investors', 'overdue_certificates', 'all_staff', 'numbers', 'file'])],
            'body' => ['required', 'string', 'max:520'],
            'placeholder' => ['nullable', Rule::in(['name', 'business_name'])],
            'numbers' => ['nullable', 'string', 'max:5000'],
            'contacts' => ['nullable', 'file', 'max:2048'],
        ]);

        if ($data['audience'] === 'all_staff' && ! $request->user()->can(AccessPermissions::SMS_MANAGE)) {
            abort(403);
        }

        $placeholder = $batch === 'directory' ? ($data['placeholder'] ?? null) : null;
        $markerError = $this->markerError($batch, (string) $data['audience'], $placeholder, $data['body']);

        if ($markerError !== null) {
            return back()->withInput()->withErrors(['body' => $markerError]);
        }

        if ($batch === 'numbers' && blank($data['numbers'] ?? null)) {
            return back()->withInput()->withErrors(['numbers' => 'Enter at least one phone number, separated by semicolons.']);
        }

        if ($batch === 'file' && ! $request->hasFile('contacts')) {
            return back()->withInput()->withErrors(['contacts' => 'Choose a CSV or Excel file of phone numbers.']);
        }

        try {
            $rows = $audience->resolve($data['audience'], $data['numbers'] ?? null, $request->file('contacts'));
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors(['contacts' => $exception->getMessage()]);
        }

        if (count($rows) > 2000) {
            return back()->withInput()->withErrors(['audience' => 'A campaign can include at most 2,000 recipients. Split the list.']);
        }

        if ($rows === []) {
            $message = $data['audience'] === 'overdue_certificates'
                ? 'Certificate renewal dates are not recorded yet, so this audience is empty.'
                : 'That audience did not match any phone number.';

            return back()->withInput()->withErrors(['audience' => $message]);
        }

        $campaign = new SmsCampaign;
        $campaign->forceFill([
            'name' => $data['name'],
            'audience' => $data['audience'],
            'body' => $data['body'],
            'personalize' => $placeholder !== null,
            'placeholder' => $placeholder,
            'status' => SmsCampaign::DRAFT,
            'recipient_count' => count($rows),
            'created_by' => $request->user()->id,
        ])->save();

        foreach ($rows as $row) {
            $recipient = new SmsCampaignRecipient;
            $recipient->forceFill([
                'sms_campaign_id' => $campaign->id,
                'phone' => $row['phone'],
                'name' => $row['name'] !== '' ? $row['name'] : null,
                'business_name' => $row['business_name'] !== '' ? $row['business_name'] : null,
                'status' => SmsCampaignRecipient::PENDING,
            ])->save();
        }

        $audit->record(
            AuditEntry::for('sms.campaign_prepared', $campaign)
                ->causedBy($request->user())
                ->withMetadata(['audience' => $campaign->audience, 'recipients' => $campaign->recipient_count, 'placeholder' => $placeholder]),
        );

        return $this->dispatch($request, $campaign, $audit);
    }

    public function show(SmsCampaign $campaign): View
    {
        return view('admin.sms.campaign', [
            'campaign' => $campaign,
            'recipients' => $campaign->recipients()->orderBy('id')->paginate(20),
        ]);
    }

    public function send(Request $request, SmsCampaign $campaign, AuditRecorder $audit): RedirectResponse
    {
        return $this->dispatch($request, $campaign, $audit);
    }

    private function dispatch(Request $request, SmsCampaign $campaign, AuditRecorder $audit): RedirectResponse
    {
        if ($campaign->status !== SmsCampaign::DRAFT) {
            return back()->withErrors(['sms' => 'This campaign has already been sent.']);
        }

        if ($campaign->audience === 'all_staff' && ! $request->user()->can(AccessPermissions::SMS_MANAGE)) {
            abort(403);
        }

        SendSmsCampaign::dispatch($campaign->id)->onQueue((string) config('sms.queue', 'default'));

        $audit->record(
            AuditEntry::for('sms.campaign_sent', $campaign)
                ->causedBy($request->user())
                ->withMetadata(['recipients' => $campaign->recipient_count]),
        );

        return redirect()->route('admin.sms.campaigns.show', $campaign)->with('status', 'The campaign is being sent through the active SMS gateway.');
    }

    private function markerError(string $batch, string $audience, ?string $placeholder, string $body): ?string
    {
        $usesName = str_contains($body, '[[NAME]]');
        $usesBusiness = str_contains($body, '[[BUSINESS_NAME]]');

        if ($batch !== 'directory') {
            if ($usesName || $usesBusiness) {
                return '[[NAME]] and [[BUSINESS_NAME]] are only for people already in IOMP. An external list is sent as written.';
            }

            return null;
        }

        if ($placeholder === null) {
            return 'Choose [[NAME]] or [[BUSINESS_NAME]]. A directory message uses one of them.';
        }

        if ($usesName && $usesBusiness) {
            return 'Use either [[NAME]] or [[BUSINESS_NAME]], not both.';
        }

        if ($audience === 'all_staff' && $placeholder !== 'name') {
            return 'Staff are addressed with [[NAME]].';
        }

        if ($placeholder === 'name' && ! $usesName) {
            return 'Include [[NAME]] once so each account name is filled in.';
        }

        if ($placeholder === 'business_name' && ! $usesBusiness) {
            return 'Include [[BUSINESS_NAME]] once so each investor profile name is filled in.';
        }

        if ($placeholder === 'name' && $usesBusiness) {
            return 'This message is set to [[NAME]]. Remove [[BUSINESS_NAME]].';
        }

        if ($placeholder === 'business_name' && $usesName) {
            return 'This message is set to [[BUSINESS_NAME]]. Remove [[NAME]].';
        }

        return null;
    }
}
