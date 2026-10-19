<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Application\StaffPortfolio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController
{
    public function __invoke(Request $request, StaffPortfolio $portfolio): View
    {
        return view('admin.dashboard', [
            'portfolio' => $portfolio->for($request->user()),
        ]);
    }
}
