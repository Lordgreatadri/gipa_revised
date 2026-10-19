<?php

namespace App\Modules\InvestorOnboarding\Presentation\Http\Controllers;

use App\Modules\InvestorEngagement\Application\InvestorOverview;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Verified investor workspace. Opportunity discovery replaces the empty
 * states when that catalogue is published. The public site still lists nothing.
 */
class InvestorDashboardController
{
    public function index(Request $request, InvestorOverview $overview): View
    {
        $user = $request->user();
        $user->load('investorProfile');

        return view('investor.dashboard', [
            'user' => $user,
            'overview' => $overview->for($user),
        ]);
    }

    public function opportunities(): View
    {
        return view('investor.placeholder', [
            'title' => 'Opportunities',
            'description' => 'Approved and active opportunities will appear here after the catalogue is published. Drafts and unapproved records stay with GIPA.',
        ]);
    }

    public function engagement(): View
    {
        return view('investor.placeholder', [
            'title' => 'My engagement',
            'description' => 'Interest and inquiry you submit against an eligible opportunity will be listed here, with the status GIPA has recorded.',
        ]);
    }

    public function account(Request $request): View
    {
        return view('investor.account', [
            'user' => $request->user(),
        ]);
    }
}
