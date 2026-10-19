<?php

namespace Database\Factories;

use App\Modules\Identity\Domain\Enums\UserType;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Default state: a fully verified investor (e-mail and phone). Use the
 * states below to build the negative cases.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => '+233'.fake()->unique()->numerify('2########'),
            'phone_verified_at' => now(),
            'user_type' => UserType::Investor,
            'country_id' => null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function phoneUnverified(): static
    {
        return $this->state(fn () => ['phone_verified_at' => null]);
    }

    /**
     * Freshly registered: neither e-mail nor phone verified.
     */
    public function registeredOnly(): static
    {
        return $this->unverified()->phoneUnverified();
    }

    public function staff(): static
    {
        return $this->state(fn () => ['user_type' => UserType::Staff]);
    }
}
