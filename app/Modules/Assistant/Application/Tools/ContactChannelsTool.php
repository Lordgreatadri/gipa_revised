<?php

namespace App\Modules\Assistant\Application\Tools;

use App\Modules\Assistant\Application\Data\ToolResult;

final class ContactChannelsTool extends AbstractTool
{
    public function name(): string
    {
        return 'contact_channels';
    }

    protected function triggers(): array
    {
        return ['contact', 'email', 'e-mail', 'telephone', 'phone number', 'call the office'];
    }

    public function handle(string $question, string $audience): ?ToolResult
    {
        $email = (string) config('public.contact.email');
        $phone = (string) config('public.contact.phone');
        $office = (string) config('public.contact.office');

        return new ToolResult(
            tool: $this->name(),
            summary: 'Use the contact page to reach the investment services team. The published e-mail is '.$email.', the published telephone is '.$phone.', and the office line is '.$office.'. Do not send passwords, one-time codes, or payment details in that form.',
            sourceLabel: 'Published contact channels',
            reference: route('contact.create'),
        );
    }
}
