<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Controllers;

use App\Modules\InvestorOnboarding\Application\ManageInvestorProfile;
use App\Modules\Shared\Domain\Models\Country;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestorProfileController
{
    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('investor.profile', [
            'user' => $user,
            'profile' => $user->investorProfile,
            'countries' => Country::query()->orderBy('name')->get(['uuid', 'name', 'abbreviation', 'currency']),
        ]);
    }

    public function update(Request $request, ManageInvestorProfile $profiles): RedirectResponse
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:160'],
            'business_email' => ['required', 'email:rfc', 'max:255'],
            'contact_phone' => ['required', 'regex:/^\+[1-9][0-9]{6,14}$/'],
            'profile_type' => ['required', 'in:individual,organization'],
            'country' => ['required', 'uuid', 'exists:countries,uuid'],
            'currency' => ['required', 'in:GHS,USD'],
            'preferred_language' => ['required', 'in:en,fr'],
        ]);

        $profiles->save($request->user(), $data);

        return redirect()->route('investor.profile')->with('status', 'Business profile saved. Inquiry and interest open after GIPA approves it.');
    }

    public function destroy(Request $request, ManageInvestorProfile $profiles): RedirectResponse
    {
        $profiles->delete($request->user(), $request->boolean('consent'));

        return redirect()->route('investor.profile')->with('status', 'The business profile was removed.');
    }
}
