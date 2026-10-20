<?php

namespace App\Modules\Shared\Presentation\Http\Controllers\Api\V1;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Identity\Application\LoginHistory\RecordLoginAttempt;
use App\Modules\Identity\Domain\IdentityAuditActions;
use App\Modules\Identity\Domain\Models\LoginAttempt;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Infrastructure\Api\JwtTokenIssuer;
use App\Modules\Shared\Presentation\Http\Api\ApiEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthTokenController
{
    public function store(Request $request, JwtTokenIssuer $tokens, AuditRecorder $audit, RecordLoginAttempt $attempts): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $email = mb_strtolower($data['email']);
        $user = User::query()->whereRaw('lower(email) = ?', [$email])->first();

        if (! $user instanceof User || ! Hash::check($data['password'], $user->password)) {
            $audit->record(
                AuditEntry::for(IdentityAuditActions::LOGIN_FAILED)
                    ->causedBy(null)
                    ->withMetadata(['guard' => 'api', 'identifier' => $email])
            );
            $attempts->record(LoginAttempt::OUTCOME_FAILED, $user instanceof User ? $user : null, $email, 'api');

            abort(401, 'The credentials are invalid.');
        }

        $audit->record(
            AuditEntry::for(IdentityAuditActions::LOGIN_SUCCEEDED, $user)
                ->causedBy($user)
                ->withMetadata(['guard' => 'api'])
        );
        $attempts->record(LoginAttempt::OUTCOME_SUCCESS, $user, $user->email, 'api');

        return ApiEnvelope::data($tokens->issue($user));
    }

    public function refresh(Request $request, JwtTokenIssuer $tokens): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string', 'max:255'],
        ]);

        return ApiEnvelope::data($tokens->refresh($data['refresh_token']));
    }

    public function revoke(Request $request, JwtTokenIssuer $tokens): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string', 'max:255'],
        ]);

        $tokens->revoke($data['refresh_token']);

        return ApiEnvelope::data(['revoked' => true]);
    }
}
