<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Modules\Identity\Application\Access\ManageAccess;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->withCount(['permissions', 'users'])->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => new Role,
            'selected' => [],
            'permissionGroups' => $this->groups(),
        ]);
    }

    public function store(Request $request, ManageAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:125', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        /** @var User $actor */
        $actor = $request->user();
        $role = $access->createRole($actor, $data['name'], array_values($data['permissions'] ?? []));

        return redirect()->route('admin.roles.edit', $role)->with('status', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role,
            'selected' => $role->permissions->pluck('name')->all(),
            'permissionGroups' => $this->groups(),
            'lockedName' => $role->name === AccessRoles::SUPER_ADMINISTRATOR,
        ]);
    }

    public function update(Request $request, Role $role, ManageAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:125', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        /** @var User $actor */
        $actor = $request->user();
        $access->updateRole($actor, $role, $data['name'], array_values($data['permissions'] ?? []));

        return redirect()->route('admin.roles.edit', $role)->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Role $role, ManageAccess $access): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();
        $access->deleteRole($actor, $role);

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted.');
    }

    /**
     * @return array<string, list<Permission>>
     */
    private function groups(): array
    {
        // toBase() keeps keyBy('name'). An Eloquent collection's only()
        // looks up primary keys, so it would drop every permission here.
        $permissions = Permission::query()->where('guard_name', 'web')->orderBy('name')->get()->keyBy('name')->toBase();
        $grouped = [];

        foreach (AccessPermissions::groups() as $label => $names) {
            $grouped[$label] = $permissions->only($names)->values()->all();
            $permissions->forget($names);
        }

        if ($permissions->isNotEmpty()) {
            $grouped['Custom'] = $permissions->values()->all();
        }

        return array_filter($grouped);
    }
}
