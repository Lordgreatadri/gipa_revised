<?php

namespace App\Modules\PublicPortal\Presentation\Http\Controllers;

use App\Modules\PublicPortal\Application\OfficeHours;
use App\Modules\PublicPortal\Application\SubmitContactEnquiry;
use App\Modules\PublicPortal\Presentation\Http\Requests\StoreContactEnquiryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController
{
    public function create(OfficeHours $officeHours): View
    {
        return view('public-portal::contact.create', [
            'office' => $officeHours->snapshot(),
            'callWindows' => [
                'Mornings 8am–12pm' => __('Mornings (8am – 12pm)'),
                'Afternoons 12pm–3pm' => __('Afternoons (12pm – 3pm)'),
                'Evenings 3pm–5pm' => __('Evenings (3pm – 5pm)'),
                'Anytime' => __('Anytime during business hours'),
            ],
        ]);
    }

    public function store(StoreContactEnquiryRequest $request, SubmitContactEnquiry $submit): RedirectResponse
    {
        $enquiry = $submit->handle($request->enquiryPayload(), $request->ip());

        return redirect()
            ->route('contact.create')
            ->with('contact_reference', $enquiry->reference)
            ->with('status', __('Thank you. Your enquiry has been received under reference :reference. We have emailed you a confirmation and our team will be in touch shortly.', [
                'reference' => $enquiry->reference,
            ]));
    }
}
