<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Domain\Models\LoginAttempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginHistoryController
{
    public function index(Request $request): View
    {
        $request->validate([
            'outcome' => ['nullable', 'in:success,failed,locked'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $term = trim((string) $request->query('q', ''));
        $outcome = (string) $request->query('outcome', '');

        $attempts = LoginAttempt::query()
            ->when($outcome !== '', fn (Builder $query) => $query->where('outcome', $outcome))
            ->when($term !== '', function (Builder $query) use ($term): void {
                $like = '%'.addcslashes(mb_substr(mb_strtolower($term), 0, 80), '%_\\').'%';
                $query->where('email', 'ilike', $like);
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.logins.index', [
            'attempts' => $attempts,
        ]);
    }
}
