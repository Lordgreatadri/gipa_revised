<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Controllers;

use App\Modules\InvestorOnboarding\Application\Registration\RegisterInvestor;
use App\Modules\InvestorOnboarding\Presentation\Http\Requests\RegisterInvestorRequest;
use App\Modules\Shared\Domain\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController
{
    public function create(): View
    {
        $countries = Country::query()->orderBy('name')->get(['uuid', 'name', 'code', 'abbreviation', 'icon_url']);
        $ghanaUuid = $countries->firstWhere('abbreviation', 'GH')?->uuid;

        return view('investor-onboarding::register', [
            'countries' => $countries,
            'defaultCountry' => $ghanaUuid,
            'defaultPhoneCountry' => $ghanaUuid,
        ]);
    }

    public function store(RegisterInvestorRequest $request, RegisterInvestor $registerInvestor): RedirectResponse
    {
        $user = $registerInvestor->handle($request->toData());

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
