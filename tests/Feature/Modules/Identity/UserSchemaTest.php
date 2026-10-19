<?php

namespace Tests\Feature\Modules\Identity;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * ADR-0003 database-level integrity for the users table.
 */
class UserSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_type_is_constrained_at_the_database(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'X',
            'email' => 'x@example.com',
            'phone' => '+233200000000',
            'user_type' => 'superuser',
            'password' => 'irrelevant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_phone_format_is_constrained_at_the_database(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => 'X',
            'email' => 'x@example.com',
            'phone' => '0200000000',
            'user_type' => 'investor',
            'password' => 'irrelevant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_user_type_cannot_be_mass_assigned(): void
    {
        $user = new User(['name' => 'X', 'user_type' => 'staff']);

        $this->assertNull($user->user_type);
    }

    public function test_the_route_key_is_the_uuid_and_the_numeric_id_is_hidden_from_urls(): void
    {
        $user = User::factory()->create();

        $this->assertSame('uuid', $user->getRouteKeyName());
        $this->assertSame($user->uuid, $user->getRouteKey());
        $this->assertTrue($user->getIncrementing());
    }
}
