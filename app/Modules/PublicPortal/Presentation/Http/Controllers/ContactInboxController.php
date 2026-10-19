<?php

namespace App\Modules\PublicPortal\Presentation\Http\Controllers;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\PublicPortal\Application\ManageContactEnquiry;
use App\Modules\PublicPortal\Domain\Models\ContactUs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactInboxController
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');

        return view('admin.contact.index', [
            'enquiries' => ContactUs::query()
                ->when(in_array($status, [ContactUs::STATUS_NEW, ContactUs::STATUS_READ, ContactUs::STATUS_RESPONDED, ContactUs::STATUS_CLOSED], true), fn ($query) => $query->where('status', $status))
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    public function show(Request $request, ContactUs $enquiry, ManageContactEnquiry $inbox): View
    {
        $inbox->markRead($request->user(), $enquiry);

        return view('admin.contact.show', [
            'enquiry' => $enquiry->load(['replies.author:id,name', 'responder:id,name']),
        ]);
    }

    public function reply(Request $request, ContactUs $enquiry, ManageContactEnquiry $inbox): RedirectResponse
    {
        abort_unless($request->user()?->can(AccessPermissions::CONTACT_MANAGE), 403);
        $data = $request->validate(['body' => ['required', 'string', 'min:10', 'max:4000']]);
        $inbox->reply($request->user(), $enquiry, $data['body']);

        return back()->with('status', 'Reply sent by e-mail. An SMS is queued when a phone number is on the enquiry.');
    }

    public function close(Request $request, ContactUs $enquiry, ManageContactEnquiry $inbox): RedirectResponse
    {
        abort_unless($request->user()?->can(AccessPermissions::CONTACT_MANAGE), 403);
        $inbox->close($request->user(), $enquiry);

        return back()->with('status', 'Enquiry closed.');
    }
}
