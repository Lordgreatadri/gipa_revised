<?php

namespace App\Modules\Shared\Application;

use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Domain\Models\InAppNotification;

/**
 * Dashboard notice. Mail and SMS stay on their own queued paths.
 */
final class NotifyUser
{
    public function send(User $user, string $title, string $body, ?string $href = null): void
    {
        $notice = new InAppNotification;
        $notice->forceFill([
            'title' => $title,
            'body' => $body,
            'href' => $href,
        ]);
        $notice->user()->associate($user);
        $notice->save();
    }

    public function sendToPermission(string $permission, string $title, string $body, ?string $href = null): void
    {
        if (! Permission::query()->where('name', $permission)->where('guard_name', 'web')->exists()) {
            return;
        }

        $this->sendEach(User::permission($permission)->get(), $title, $body, $href);
    }

    /**
     * @param  iterable<int, User>  $users
     */
    public function sendEach(iterable $users, string $title, string $body, ?string $href = null): void
    {
        foreach ($users as $user) {
            $this->send($user, $title, $body, $href);
        }
    }
}
