<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use Illuminate\View\View;

class ReportingController
{
    public function __invoke(): View
    {
        return view('admin.reports');
    }
}
