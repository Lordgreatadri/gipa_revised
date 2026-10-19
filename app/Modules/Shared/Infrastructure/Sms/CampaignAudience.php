<?php

namespace App\Modules\Shared\Infrastructure\Sms;

use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileOnboardingState;
use App\Modules\InvestorOnboarding\Domain\Enums\ProfileStatus;
use Illuminate\Http\UploadedFile;

/**
 * Builds the recipient snapshot for one campaign. Certificate renewal is listed
 * because the requirements include certificates, but those records are not
 * stored yet, so that audience is empty until they are.
 */
final class CampaignAudience
{
    public function __construct(
        private readonly SmsGateway $gateway,
        private readonly ContactListParser $parser,
    ) {}

    /**
     * @return list<array{phone: string, name: string, business_name: string}>
     */
    public function resolve(string $audience, ?string $numbers, ?UploadedFile $file): array
    {
        $rows = match ($audience) {
            'active_investors' => $this->investors(activeOnly: true),
            'all_investors' => $this->investors(activeOnly: false),
            'overdue_certificates' => [],
            'all_staff' => $this->staff(),
            'numbers' => $this->parser->semicolon((string) $numbers),
            'file' => $file === null ? [] : $this->parser->file($file),
            default => [],
        };

        $unique = [];

        foreach ($rows as $row) {
            $phone = $this->gateway->normalize($row['phone']);

            if ($phone === null || isset($unique[$phone])) {
                continue;
            }

            $unique[$phone] = [
                'phone' => $phone,
                'name' => trim($row['name']),
                'business_name' => trim($row['business_name']),
            ];
        }

        return array_values($unique);
    }

    /**
     * @return list<array{phone: string, name: string, business_name: string}>
     */
    private function investors(bool $activeOnly): array
    {
        $query = User::query()
            ->where('user_type', UserType::Investor)
            ->whereNotNull('phone')
            ->with('investorProfile');

        if ($activeOnly) {
            $query->whereHas('investorProfile', function ($profile): void {
                $profile->where('status', ProfileStatus::Active->value)
                    ->where('onboarding_state', ProfileOnboardingState::Approved->value);
            });
        }

        return $query->get()->map(fn (User $user): array => [
            'phone' => (string) $user->phone,
            'name' => (string) $user->name,
            'business_name' => (string) ($user->investorProfile?->organization_name ?: $user->name),
        ])->all();
    }

    /**
     * @return list<array{phone: string, name: string, business_name: string}>
     */
    private function staff(): array
    {
        return User::query()
            ->where('user_type', UserType::Staff)
            ->whereNotNull('phone')
            ->get()
            ->map(fn (User $user): array => [
                'phone' => (string) $user->phone,
                'name' => (string) $user->name,
                'business_name' => 'GIPA',
            ])
            ->all();
    }
}
