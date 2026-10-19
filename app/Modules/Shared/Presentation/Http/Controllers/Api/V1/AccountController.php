<?php

namespace App\Modules\Shared\Presentation\Http\Controllers\Api\V1;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Presentation\Http\Api\ApiEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiEnvelope::data([
            'id' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'account_type' => $user->isStaff() ? 'staff' : 'investor',
            'email_verified' => $user->hasVerifiedEmail(),
            'phone_verified' => $user->hasVerifiedPhone(),
        ]);
    }
}
