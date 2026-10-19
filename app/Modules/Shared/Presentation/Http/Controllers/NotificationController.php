<?php

namespace App\Modules\Shared\Presentation\Http\Controllers;

use App\Modules\Shared\Domain\Models\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => InAppNotification::query()
                ->where('user_id', $request->user()->id)
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function read(Request $request, InAppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return redirect($notification->href ?: route('notifications.index'));
    }
}
