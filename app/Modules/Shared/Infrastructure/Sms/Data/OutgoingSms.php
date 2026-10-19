<?php

namespace App\Modules\Shared\Infrastructure\Sms\Data;

final readonly class OutgoingSms
{
    public function __construct(
        public string $reference,
        public string $recipient,
        public string $body,
        public string $senderId,
        public ?string $callbackUrl = null,
    ) {}
}
