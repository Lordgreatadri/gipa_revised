<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $campaignId) {}

    public function handle(SmsGateway $gateway): void
    {
        $campaign = SmsCampaign::query()->find($this->campaignId);

        if ($campaign === null || $campaign->status === SmsCampaign::COMPLETED) {
            return;
        }

        $campaign->forceFill(['status' => SmsCampaign::SENDING])->save();
        $sent = 0;
        $failed = 0;

        $campaign->recipients()->where('status', SmsCampaignRecipient::PENDING)->orderBy('id')->each(function (SmsCampaignRecipient $recipient) use ($gateway, $campaign, &$sent, &$failed): void {
            $body = match ($campaign->placeholder) {
                'name' => str_replace('[[NAME]]', (string) $recipient->name, $campaign->body),
                'business_name' => str_replace('[[BUSINESS_NAME]]', (string) $recipient->business_name, $campaign->body),
                default => $campaign->personalize
                    ? str_replace(
                        ['[[NAME]]', '[[BUSINESS_NAME]]'],
                        [(string) $recipient->name, (string) $recipient->business_name],
                        $campaign->body,
                    )
                    : $campaign->body,
            };

            if (mb_strlen($body) > 520) {
                $recipient->forceFill(['status' => SmsCampaignRecipient::SKIPPED, 'detail' => 'Rendered message exceeds 520 characters.'])->save();
                $failed++;

                return;
            }

            $message = $gateway->queue($recipient->phone, $body, 'sms.campaign');

            if ($message === null || $message->status === SmsMessage::FAILED) {
                $recipient->forceFill([
                    'status' => SmsCampaignRecipient::FAILED,
                    'sms_message_id' => $message?->id,
                    'detail' => 'The gateway did not accept the message.',
                ])->save();
                $failed++;

                return;
            }

            $message->forceFill(['campaign_id' => $campaign->id])->save();
            $recipient->forceFill([
                'status' => SmsCampaignRecipient::SENT,
                'sms_message_id' => $message->id,
            ])->save();
            $sent++;
        });

        $campaign->forceFill([
            'status' => $sent === 0 ? SmsCampaign::FAILED : SmsCampaign::COMPLETED,
            'sent_count' => $sent,
            'failed_count' => $failed,
        ])->save();
    }
}
