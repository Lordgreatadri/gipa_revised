<?php

namespace App\Modules\InvestorEngagement\Presentation\Http\Controllers;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\InvestorEngagement\Application\PostEngagementMessage;
use App\Modules\InvestorEngagement\Domain\Models\InvestorEngagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestorEngagementController
{
    public function index(Request $request): View
    {
        /** @var User $investor */
        $investor = $request->user();

        return view('investor.engagement', [
            'engagements' => InvestorEngagement::query()
                ->where('investor_id', $investor->id)
                ->with(['opportunity:id,uuid,title,reference'])
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function show(Request $request, InvestorEngagement $engagement): View
    {
        abort_unless($engagement->investor_id === $request->user()->id, 404);

        return view('investor.engagement-show', [
            'engagement' => $engagement->load(['opportunity:id,uuid,title', 'messages.author:id,name']),
        ]);
    }

    public function message(Request $request, InvestorEngagement $engagement, PostEngagementMessage $messages): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'min:10', 'max:2000']]);
        $messages->post($request->user(), $engagement, $data['body']);

        return back()->with('status', 'Message sent to GIPA.');
    }
}
