<?php

namespace App\Modules\InvestorOnboarding\Presentation\Navigation;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;

final class InvestorMenu
{
    /**
     * @return list<array{label: string, items: list<array{label: string, route: string, icon: string, active: string, permission: string}>}>
     */
    public function groups(User $user): array
    {
        $needsProfile = $user->investorProfile === null;
        $items = array_values(array_filter([
            [
                'label' => 'Overview',
                'route' => 'investor.dashboard',
                'icon' => 'layout-dashboard',
                'active' => 'investor.dashboard',
                'permission' => AccessPermissions::INVESTOR_DASHBOARD,
            ],
            [
                'label' => 'Opportunities',
                'route' => 'investor.opportunities',
                'icon' => 'map-pinned',
                'active' => 'investor.opportunities*',
                'permission' => AccessPermissions::INVESTOR_OPPORTUNITIES,
            ],
            [
                'label' => 'Investment map',
                'route' => 'investor.map',
                'icon' => 'map',
                'active' => 'investor.map*',
                'permission' => AccessPermissions::INVESTOR_OPPORTUNITIES,
            ],
            [
                'label' => 'My engagement',
                'route' => 'investor.engagement',
                'icon' => 'handshake',
                'active' => 'investor.engagement*',
                'permission' => AccessPermissions::INVESTOR_ENGAGEMENT,
            ],
            [
                'label' => 'Business profile',
                'route' => 'investor.profile',
                'icon' => 'building-2',
                'active' => 'investor.profile*',
                'permission' => AccessPermissions::INVESTOR_DASHBOARD,
            ],
            [
                'label' => 'Account',
                'route' => 'investor.account',
                'icon' => 'user',
                'active' => 'investor.account',
                'permission' => AccessPermissions::INVESTOR_DASHBOARD,
            ],
        ], fn (array $item): bool => $user->can($item['permission']) && (! $needsProfile || $item['route'] === 'investor.profile')));

        if ($items === []) {
            return [];
        }

        return [
            [
                'label' => 'Investor workspace',
                'items' => $items,
            ],
        ];
    }
}
