<?php

namespace App\Modules\InvestorOnboarding\Infrastructure\PhoneVerification;

use App\Modules\InvestorOnboarding\Application\Contracts\PhoneVerificationSender;
use App\Modules\InvestorOnboarding\Application\PhoneVerification\PhoneVerificationMessage;

/**
 * In-memory sender for automated tests. Bound as a singleton so a test can
 * read back what was "sent".
 */
final class ArrayPhoneVerificationSender implements PhoneVerificationSender
{
    /** @var list<PhoneVerificationMessage> */
    private array $sent = [];

    public function send(PhoneVerificationMessage $message): void
    {
        $this->sent[] = $message;
    }

    /**
     * @return list<PhoneVerificationMessage>
     */
    public function sent(): array
    {
        return $this->sent;
    }

    public function last(): ?PhoneVerificationMessage
    {
        return $this->sent === [] ? null : $this->sent[array_key_last($this->sent)];
    }

    public function flush(): void
    {
        $this->sent = [];
    }
}
