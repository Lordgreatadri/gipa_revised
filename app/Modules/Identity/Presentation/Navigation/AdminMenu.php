<?php

namespace App\Modules\Identity\Presentation\Navigation;

use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;

final class AdminMenu
{
    /**
     * @return list<array{label: string, items: list<array{label: string, route: string, icon: string, active: string|list<string>, permission: string}>}>
     */
    public function groups(User $user): array
    {
        return $this->visible($user, [
            [
                'label' => 'Overview',
                'items' => [
                    $this->item('Dashboard', 'admin.dashboard', 'layout-dashboard', 'admin.dashboard', AccessPermissions::ADMIN_DASHBOARD),
                    $this->item('Reporting', 'admin.reports', 'chart-column', 'admin.reports', AccessPermissions::ADMIN_DASHBOARD),
                ],
            ],
            [
                'label' => 'Access',
                'items' => [
                    $this->item('Users', 'admin.users.index', 'users', 'admin.users.*', AccessPermissions::USERS_VIEW),
                    $this->item('Roles', 'admin.roles.index', 'shield', 'admin.roles.*', AccessPermissions::ROLES_VIEW),
                    $this->item('Permissions', 'admin.permissions.index', 'key-round', 'admin.permissions.*', AccessPermissions::PERMISSIONS_VIEW),
                ],
            ],
            [
                'label' => 'Records',
                'items' => [
                    $this->item('Opportunity Overview', 'admin.opportunities.index', 'chart-column', 'admin.opportunities.index', AccessPermissions::OPPORTUNITIES_VIEW),
                    $this->item('Opportunity Record', 'admin.opportunities.register', 'list', ['admin.opportunities.register', 'admin.opportunities.show', 'admin.opportunities.edit', 'admin.opportunities.create'], AccessPermissions::OPPORTUNITIES_VIEW),
                    $this->item('Regions', 'admin.regions.index', 'map', 'admin.regions.*', AccessPermissions::OPPORTUNITIES_VIEW),
                    $this->item('Districts', 'admin.districts.index', 'map-pin', ['admin.districts.index', 'admin.districts.register'], AccessPermissions::OPPORTUNITIES_VIEW),
                    $this->item('Sectors', 'admin.sectors.index', 'layers', ['admin.sectors.index', 'admin.sectors.register'], AccessPermissions::CATALOGUE_MANAGE),
                    $this->item('Sub-sectors', 'admin.sub-sectors.index', 'factory', ['admin.sub-sectors.index', 'admin.sub-sectors.register'], AccessPermissions::CATALOGUE_MANAGE),
                    $this->item('Enterprise types', 'admin.types.index', 'building-2', ['admin.types.index', 'admin.types.register'], AccessPermissions::CATALOGUE_MANAGE),
                    $this->item('Engagement', 'admin.engagement', 'handshake', 'admin.engagement*', AccessPermissions::ENGAGEMENT_VIEW),
                    $this->item('Investor profiles', 'admin.profiles.index', 'badge-check', 'admin.profiles.*', AccessPermissions::PROFILES_REVIEW),
                    $this->item('Contact enquiries', 'admin.contact', 'mail', 'admin.contact*', AccessPermissions::CONTACT_VIEW),
                ],
            ],
            [
                'label' => 'Assurance',
                'items' => [
                    $this->item('Audit log', 'admin.audit', 'scroll-text', 'admin.audit*', AccessPermissions::AUDIT_VIEW),
                    $this->item('Login history', 'admin.logins.index', 'log-in', 'admin.logins.*', AccessPermissions::LOGINS_VIEW),
                    $this->item('SMS gateways', 'admin.sms.index', 'smartphone', ['admin.sms.index', 'admin.sms.edit'], AccessPermissions::SMS_MANAGE),
                    $this->item('SMS messages', 'admin.sms.messages', 'list', 'admin.sms.messages*', AccessPermissions::SMS_MESSAGES_VIEW),
                    $this->item('Bulk SMS', 'admin.sms.campaigns', 'send', 'admin.sms.campaigns*', AccessPermissions::SMS_CAMPAIGNS_MANAGE),
                    $this->item('Assistant knowledge', 'admin.assistant.knowledge.index', 'scroll-text', 'admin.assistant.knowledge.*', AccessPermissions::ASSISTANT_KNOWLEDGE_VIEW),
                    $this->item('Database backups', 'admin.backups.index', 'database-backup', 'admin.backups.*', AccessPermissions::BACKUPS_VIEW),
                ],
            ],
        ]);
    }

    /**
     * @param  list<array{label: string, items: list<array{label: string, route: string, icon: string, active: string|list<string>, permission: string}>}>  $groups
     * @return list<array{label: string, items: list<array{label: string, route: string, icon: string, active: string|list<string>, permission: string}>}>
     */
    private function visible(User $user, array $groups): array
    {
        $visible = [];

        foreach ($groups as $group) {
            $items = array_values(array_filter(
                $group['items'],
                fn (array $item): bool => $user->can($item['permission']),
            ));

            if ($items !== []) {
                $group['items'] = $items;
                $visible[] = $group;
            }
        }

        return $visible;
    }

    /**
     * @param  string|list<string>  $active
     * @return array{label: string, route: string, icon: string, active: string|list<string>, permission: string}
     */
    private function item(string $label, string $route, string $icon, string|array $active, string $permission): array
    {
        return compact('label', 'route', 'icon', 'active', 'permission');
    }
}
