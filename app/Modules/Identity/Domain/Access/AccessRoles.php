<?php

namespace App\Modules\Identity\Domain\Access;

use App\Modules\Identity\Domain\Models\Permission;
use App\Modules\Identity\Domain\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Initial role catalogue from ADR-0006 and the role names already shown to
 * the client. Permission sets are the starting point; an administrator can
 * change them afterwards. A later seed does not overwrite a role that
 * already has permissions.
 */
final class AccessRoles
{
    public const SUPER_ADMINISTRATOR = 'Super Administrator';

    public const CONTENT_MANAGER = 'Content / Data Manager';

    public const DISTRICT_OFFICER = 'District Officer';

    public const FIELD_AGENT = 'Field Agent';

    public const REVIEWER = 'Reviewer / Approver';

    public const INVESTOR = 'Investor';

    /**
     * @return array<string, list<string>>
     */
    public static function defaults(): array
    {
        return [
            self::SUPER_ADMINISTRATOR => AccessPermissions::ALL,
            self::CONTENT_MANAGER => [
                AccessPermissions::ADMIN_DASHBOARD,
                AccessPermissions::OPPORTUNITIES_VIEW,
                AccessPermissions::OPPORTUNITIES_CREATE,
                AccessPermissions::OPPORTUNITIES_UPDATE,
                AccessPermissions::OPPORTUNITIES_SUBMIT,
                AccessPermissions::CATALOGUE_MANAGE,
                AccessPermissions::CONTACT_VIEW,
                AccessPermissions::CONTACT_MANAGE,
                AccessPermissions::ENGAGEMENT_VIEW,
                AccessPermissions::SMS_MESSAGES_VIEW,
                AccessPermissions::SMS_CAMPAIGNS_MANAGE,
                AccessPermissions::ASSISTANT_KNOWLEDGE_VIEW,
                AccessPermissions::ASSISTANT_KNOWLEDGE_MANAGE,
                AccessPermissions::BACKUPS_VIEW,
                AccessPermissions::BACKUPS_MANAGE,
                AccessPermissions::BACKUPS_DOWNLOAD,
            ],
            self::DISTRICT_OFFICER => [
                AccessPermissions::ADMIN_DASHBOARD,
                AccessPermissions::OPPORTUNITIES_VIEW,
                AccessPermissions::OPPORTUNITIES_CREATE,
                AccessPermissions::OPPORTUNITIES_UPDATE,
                AccessPermissions::OPPORTUNITIES_SUBMIT,
            ],
            self::FIELD_AGENT => [
                AccessPermissions::ADMIN_DASHBOARD,
                AccessPermissions::OPPORTUNITIES_VIEW,
                AccessPermissions::OPPORTUNITIES_CREATE,
                AccessPermissions::OPPORTUNITIES_SUBMIT,
            ],
            self::REVIEWER => [
                AccessPermissions::ADMIN_DASHBOARD,
                AccessPermissions::OPPORTUNITIES_VIEW,
                AccessPermissions::OPPORTUNITIES_APPROVE,
                AccessPermissions::OPPORTUNITIES_REJECT,
                AccessPermissions::OPPORTUNITIES_REASSIGN,
                AccessPermissions::OPPORTUNITIES_ACTIVATE,
                AccessPermissions::OPPORTUNITIES_COMPLETE,
                AccessPermissions::OPPORTUNITIES_CANCEL,
                AccessPermissions::ENGAGEMENT_VIEW,
                AccessPermissions::ENGAGEMENT_REVIEW,
                AccessPermissions::PROFILES_REVIEW,
                AccessPermissions::CONTACT_VIEW,
                AccessPermissions::AUDIT_VIEW,
            ],
            self::INVESTOR => [
                AccessPermissions::INVESTOR_DASHBOARD,
                AccessPermissions::INVESTOR_OPPORTUNITIES,
                AccessPermissions::INVESTOR_ENGAGEMENT,
            ],
        ];
    }

    public static function investorRole(): Role
    {
        $role = Role::findOrCreate(self::INVESTOR, 'web');

        if ($role->permissions()->doesntExist()) {
            foreach (self::defaults()[self::INVESTOR] as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $role->syncPermissions(self::defaults()[self::INVESTOR]);
        }

        return $role;
    }
}
