<?php

namespace App\Modules\Opportunity\Application;

use App\Modules\Geography\Domain\Models\District;
use App\Modules\Identity\Domain\Access\AccessRoles;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Opportunity\Domain\Models\Opportunity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * District Officer and Field Agent work inside assigned districts.
 * National roles (super administrator, content manager, reviewer) are not
 * limited by that assignment.
 */
final class DistrictScope
{
    public function isRestricted(User $user): bool
    {
        if (! $user->isStaff()) {
            return false;
        }

        if ($user->hasAnyRole([
            AccessRoles::SUPER_ADMINISTRATOR,
            AccessRoles::CONTENT_MANAGER,
            AccessRoles::REVIEWER,
        ])) {
            return false;
        }

        return $user->hasAnyRole([
            AccessRoles::DISTRICT_OFFICER,
            AccessRoles::FIELD_AGENT,
        ]);
    }

    /**
     * @param  Builder<Opportunity>  $query
     */
    public function constrain(Builder $query, User $user): void
    {
        if (! $this->isRestricted($user)) {
            return;
        }

        $table = $query->getModel()->getTable();

        $query->whereIn($table.'.district_id', $user->districts()->select('districts.id'));
        $query->where(function (Builder $inner) use ($user, $table): void {
            $inner->where($table.'.created_by', $user->id)
                ->orWhere($table.'.assigned_to', $user->id)
                ->orWhereIn($table.'.id', function ($sub) use ($user): void {
                    $sub->select('opportunity_id')
                        ->from('opportunity_transitions')
                        ->where('actor_id', $user->id);
                });
        });
    }

    public function canSee(User $user, Opportunity $opportunity): bool
    {
        if (! $this->isRestricted($user)) {
            return true;
        }

        if (! $user->districts()->whereKey($opportunity->district_id)->exists()) {
            return false;
        }

        return $opportunity->created_by === $user->id
            || $opportunity->assigned_to === $user->id
            || $opportunity->transitions()->where('actor_id', $user->id)->exists();
    }

    public function assertMayUse(User $user, District $district): void
    {
        if (! $this->isRestricted($user)) {
            return;
        }

        if ($user->districts()->whereKey($district->id)->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'district_id' => 'You can only record opportunities in a district assigned to you.',
        ]);
    }
}
