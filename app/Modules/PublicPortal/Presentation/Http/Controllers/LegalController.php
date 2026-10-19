<?php

namespace App\Modules\PublicPortal\Presentation\Http\Controllers;

use App\Modules\Shared\Infrastructure\Api\ApiReferencePresenter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalController
{
    public function privacy(): View
    {
        return $this->page(
            __('Privacy'),
            __('How IOMP will describe the personal data it holds, why it is held, and how you can ask about it. This page is a placeholder until GIPA publishes the approved notice.'),
        );
    }

    public function terms(): View
    {
        return $this->page(
            __('Terms of use'),
            __('The conditions for using the Investment Opportunities Mapping Platform, including investor registration and staff access by invitation. This page is a placeholder until GIPA publishes the approved terms.'),
        );
    }

    public function dataPolicy(): View
    {
        return $this->page(
            __('Data policy'),
            __('How opportunity records, investor engagement, and operational logs are classified, retained, and shared. This page is a placeholder until GIPA publishes the approved policy.'),
        );
    }

    public function guide(): View
    {
        return view('public-portal::legal.guide');
    }

    public function apiDocumentation(Request $request, ApiReferencePresenter $reference): View
    {
        $query = trim((string) $request->query('q', ''));

        return view('public-portal::legal.api', [
            'query' => $query,
            'operations' => $reference->operations($query),
            'baseUrl' => rtrim((string) config('app.url'), '/').'/api/v1',
        ]);
    }

    private function page(string $title, string $summary): View
    {
        return view('public-portal::legal.placeholder', [
            'title' => $title,
            'summary' => $summary,
        ]);
    }
}
