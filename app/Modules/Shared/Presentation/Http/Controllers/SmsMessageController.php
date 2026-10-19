<?php

namespace App\Modules\Shared\Presentation\Http\Controllers;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Shared\Infrastructure\Sms\SmsDeliveryEvent;
use App\Modules\Shared\Infrastructure\Sms\SmsGateway;
use App\Modules\Shared\Infrastructure\Sms\SmsMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsMessageController
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $allowed = [SmsMessage::QUEUED, SmsMessage::SENT, SmsMessage::DELIVERED, SmsMessage::FAILED, SmsMessage::UNDELIVERED, SmsMessage::SKIPPED];

        $messages = SmsMessage::query()
            ->when(in_array($status, $allowed, true), fn ($query) => $query->where('status', $status))
            ->when(filled($request->query('q')), function ($query) use ($request): void {
                $term = '%'.addcslashes((string) $request->query('q'), '%_\\').'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('reference', 'ilike', $term)->orWhere('recipient', 'ilike', $term);
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sms.messages', [
            'messages' => $messages,
            'status' => $status,
        ]);
    }

    public function show(SmsMessage $message): View
    {
        return view('admin.sms.message', [
            'message' => $message,
            'events' => SmsDeliveryEvent::query()->where('sms_message_id', $message->id)->latest('id')->get(),
        ]);
    }

    public function resend(SmsMessage $message, SmsGateway $gateway, AuditRecorder $audit, Request $request): RedirectResponse
    {
        if (! $message->canBeResent()) {
            return back()->withErrors(['sms' => 'Only a failed message, or one still waiting after an hour, can be sent again.']);
        }

        $copy = $gateway->queue($message->recipient, $message->body, 'sms.resend');

        if ($copy === null) {
            return back()->withErrors(['sms' => 'No SMS gateway is active, so the message was not sent again.']);
        }

        $copy->forceFill(['resent_from_id' => $message->id])->save();

        $audit->record(
            AuditEntry::for('sms.resent', $copy)
                ->causedBy($request->user())
                ->withMetadata(['from' => $message->reference, 'status' => $copy->status]),
        );

        return redirect()->route('admin.sms.messages.show', $copy)->with('status', 'The message was queued again as '.$copy->reference.'.');
    }
}
