<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Controllers;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\InvestorOnboarding\Application\ManageInvestorProfile;
use App\Modules\InvestorOnboarding\Domain\Models\InvestorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestorProfileReviewController
{
    public function index(): View
    {
        return view('admin.profiles.index', [
            'profiles' => InvestorProfile::query()->with('user:id,uuid,name,email')->latest('id')->paginate(20),
        ]);
    }

    public function approve(Request $request, InvestorProfile $profile, ManageInvestorProfile $profiles): RedirectResponse
    {
        abort_unless($request->user()?->can(AccessPermissions::PROFILES_REVIEW), 403);
        $profiles->approve($request->user(), $profile);

        return back()->with('status', 'Profile approved. The investor can now inquire and express interest.');
    }
}
