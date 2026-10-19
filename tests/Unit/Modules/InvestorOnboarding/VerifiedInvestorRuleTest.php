<?php

namespace Tests\Unit\Modules\InvestorOnboarding;

use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorOnboarding\Domain\OnboardingStep;
use App\Modules\InvestorOnboarding\Domain\Rules\VerifiedInvestorRule;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pure rule test: no database is touched, but Eloquent attribute casting
 * needs the application container, so the Laravel TestCase is used.
 */
class VerifiedInvestorRuleTest extends TestCase
{
    /**
     * @return array<string, array{UserType, bool, bool, bool}>
     */
    public static function combinations(): array
    {
        return [
            'investor, nothing verified' => [UserType::Investor, false, false, false],
            'investor, email only' => [UserType::Investor, true, false, false],
            'investor, phone only' => [UserType::Investor, false, true, false],
            'investor, both verified' => [UserType::Investor, true, true, true],
            'staff, both verified' => [UserType::Staff, true, true, false],
            'staff, nothing verified' => [UserType::Staff, false, false, false],
        ];
    }

    #[DataProvider('combinations')]
    public function test_only_an_investor_with_both_facts_is_verified(UserType $type, bool $email, bool $phone, bool $expected): void
    {
        $user = $this->user($type, $email, $phone);

        $this->assertSame($expected, VerifiedInvestorRule::satisfiedBy($user));
    }

    public function test_next_step_follows_the_confirmed_order_email_then_phone(): void
    {
        $this->assertSame(OnboardingStep::VerifyEmail, OnboardingStep::nextFor($this->user(UserType::Investor, false, false)));
        $this->assertSame(OnboardingStep::VerifyEmail, OnboardingStep::nextFor($this->user(UserType::Investor, false, true)));
        $this->assertSame(OnboardingStep::VerifyPhone, OnboardingStep::nextFor($this->user(UserType::Investor, true, false)));
        $this->assertNull(OnboardingStep::nextFor($this->user(UserType::Investor, true, true)));
    }

    private function user(UserType $type, bool $emailVerified, bool $phoneVerified): User
    {
        $user = new User;
        $user->user_type = $type;
        $user->email_verified_at = $emailVerified ? CarbonImmutable::now() : null;
        $user->phone_verified_at = $phoneVerified ? CarbonImmutable::now() : null;

        return $user;
    }
}
