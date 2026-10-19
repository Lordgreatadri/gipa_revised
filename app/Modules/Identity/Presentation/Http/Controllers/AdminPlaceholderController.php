<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use Illuminate\View\View;

class AdminPlaceholderController
{
    public function opportunities(): View
    {
        return $this->page(
            'Opportunities',
            'District officers will create draft opportunities here. Reviewers approve and activate them. Nothing in this list is shown to the public.',
        );
    }

    public function engagement(): View
    {
        return $this->page(
            'Engagement',
            'Interest and inquiry from verified investors will be reviewed here by staff who hold the engagement review permission.',
        );
    }

    public function contact(): View
    {
        return $this->page(
            'Contact enquiries',
            'Public contact messages are already stored. The staff inbox for reading and closing them will be added in the management slice.',
        );
    }

    public function audit(): View
    {
        return $this->page(
            'Audit log',
            'Sign-in, role changes and permission changes are already written to the audit store. The viewer for those records will be added with the assurance slice.',
        );
    }

    private function page(string $title, string $description): View
    {
        return view('admin.placeholder', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}
