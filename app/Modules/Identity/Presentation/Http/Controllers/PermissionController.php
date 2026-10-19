<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Application\Access\ManageAccess;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController
{
    public function index(): View
    {
        $permissions = Permission::query()->withCount('roles')->orderBy('name')->get();
        $known = collect(AccessPermissions::groups())->flatten();

        return view('admin.permissions.index', [
            'groups' => collect(AccessPermissions::groups())->map(
                fn (array $names) => $permissions->whereIn('name', $names)->values(),
            ),
            'custom' => $permissions->reject(fn (Permission $permission) => $known->contains($permission->name))->values(),
        ]);
    }

    public function store(Request $request, ManageAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:125',
                'regex:/^[a-z0-9]+(?:[.][a-z0-9]+)+$/',
                Rule::unique('permissions', 'name')->where('guard_name', 'web'),
            ],
        ]);

        /** @var User $actor */
        $actor = $request->user();
        $access->createPermission($actor, $data['name']);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission created.');
    }

    public function destroy(Request $request, Permission $permission, ManageAccess $access): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $access->deletePermission($actor, $permission);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission deleted.');
    }
}
