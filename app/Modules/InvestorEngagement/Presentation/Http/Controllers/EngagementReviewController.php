<?php

namespace App\Modules\InvestorEngagement\Presentation\Http\Controllers;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Application\PostEngagementMessage;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use App\Modules\Opportunity\Application\DistrictScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngagementReviewController
{
    public function index(Request $request, DistrictScope $scope): View
    {
        /** @var User $actor */
        $actor = $request->user();

        $query = InvestorEngagement::query()
            ->with([
                'investor:id,name',
                'opportunity:id,uuid,title,reference,district_id',
                'opportunity.district:id,name',
            ])
            ->latest('id');

        if ($scope->isRestricted($actor)) {
            $query->whereHas('opportunity', function ($opportunities) use ($scope, $actor): void {
                $scope->constrain($opportunities, $actor);
            });
        }

        return view('admin.engagement.index', [
            'engagements' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show(Request $request, InvestorEngagement $engagement, DistrictScope $scope): View
    {
        abort_unless($engagement->opportunity !== null && $scope->canSee($request->user(), $engagement->opportunity), 404);

        return view('admin.engagement.show', [
            'engagement' => $engagement->load(['investor:id,name,email', 'opportunity:id,uuid,title,reference', 'messages.author:id,name']),
        ]);
    }

    public function message(Request $request, InvestorEngagement $engagement, PostEngagementMessage $messages): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'min:10', 'max:2000']]);
        $messages->post($request->user(), $engagement, $data['body']);

        return back()->with('status', 'Message sent to the investor.');
    }
}
