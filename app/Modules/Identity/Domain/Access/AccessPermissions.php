<?php

namespace App\Modules\Identity\Domain\Access;

/**
 * Permission names seeded for the administrative UI (ADR-0006).
 *
 * Workflow names follow the transitions the authorization ADR lists as
 * separate permissions. District scope is still AMB-14, so these names do
 * not by themselves grant another district.
 */
final class AccessPermissions
{
    public const ADMIN_DASHBOARD = 'admin.dashboard.view';

    public const USERS_VIEW = 'access.users.view';

    public const USERS_ASSIGN_ROLES = 'access.users.assign-roles';

    public const ROLES_VIEW = 'access.roles.view';

    public const ROLES_MANAGE = 'access.roles.manage';

    public const PERMISSIONS_VIEW = 'access.permissions.view';

    public const PERMISSIONS_MANAGE = 'access.permissions.manage';

    public const OPPORTUNITIES_VIEW = 'opportunities.view';

    public const OPPORTUNITIES_CREATE = 'opportunities.create';

    public const OPPORTUNITIES_UPDATE = 'opportunities.update';

    public const OPPORTUNITIES_SUBMIT = 'opportunities.submit';

    public const OPPORTUNITIES_APPROVE = 'opportunities.approve';

    public const OPPORTUNITIES_REJECT = 'opportunities.reject';

    public const OPPORTUNITIES_REASSIGN = 'opportunities.reassign';

    public const OPPORTUNITIES_ACTIVATE = 'opportunities.activate';

    public const OPPORTUNITIES_COMPLETE = 'opportunities.complete';

    public const OPPORTUNITIES_CANCEL = 'opportunities.cancel';

    public const CATALOGUE_MANAGE = 'catalogue.manage';

    public const ENGAGEMENT_VIEW = 'engagement.view';

    public const ENGAGEMENT_REVIEW = 'engagement.review';

    public const CONTACT_VIEW = 'contact.view';

    public const CONTACT_MANAGE = 'contact.manage';

    public const AUDIT_VIEW = 'audit.view';

    public const SMS_MANAGE = 'sms.manage';

    public const SMS_MESSAGES_VIEW = 'sms.messages.view';

    public const SMS_CAMPAIGNS_MANAGE = 'sms.campaigns.manage';

    public const INVESTOR_DASHBOARD = 'investor.dashboard.view';

    public const INVESTOR_OPPORTUNITIES = 'investor.opportunities.view';

    public const INVESTOR_ENGAGEMENT = 'investor.engagement.create';

    public const PROFILES_REVIEW = 'investor.profiles.review';

    public const ASSISTANT_KNOWLEDGE_VIEW = 'assistant.knowledge.view';

    public const ASSISTANT_KNOWLEDGE_MANAGE = 'assistant.knowledge.manage';

    public const BACKUPS_VIEW = 'backups.view';

    public const BACKUPS_MANAGE = 'backups.manage';

    public const BACKUPS_DOWNLOAD = 'backups.download';

    /** @var list<string> */
    public const ALL = [
        self::ADMIN_DASHBOARD,
        self::USERS_VIEW,
        self::USERS_ASSIGN_ROLES,
        self::ROLES_VIEW,
        self::ROLES_MANAGE,
        self::PERMISSIONS_VIEW,
        self::PERMISSIONS_MANAGE,
        self::OPPORTUNITIES_VIEW,
        self::OPPORTUNITIES_CREATE,
        self::OPPORTUNITIES_UPDATE,
        self::OPPORTUNITIES_SUBMIT,
        self::OPPORTUNITIES_APPROVE,
        self::OPPORTUNITIES_REJECT,
        self::OPPORTUNITIES_REASSIGN,
        self::OPPORTUNITIES_ACTIVATE,
        self::OPPORTUNITIES_COMPLETE,
                self::OPPORTUNITIES_CANCEL,
                self::CATALOGUE_MANAGE,
                self::ENGAGEMENT_VIEW,
        self::ENGAGEMENT_REVIEW,
        self::CONTACT_VIEW,
        self::CONTACT_MANAGE,
        self::AUDIT_VIEW,
        self::SMS_MANAGE,
        self::SMS_MESSAGES_VIEW,
        self::SMS_CAMPAIGNS_MANAGE,
        self::INVESTOR_DASHBOARD,
        self::INVESTOR_OPPORTUNITIES,
        self::INVESTOR_ENGAGEMENT,
        self::PROFILES_REVIEW,
        self::ASSISTANT_KNOWLEDGE_VIEW,
        self::ASSISTANT_KNOWLEDGE_MANAGE,
        self::BACKUPS_VIEW,
        self::BACKUPS_MANAGE,
        self::BACKUPS_DOWNLOAD,
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function groups(): array
    {
        return [
            'Administration' => [
                self::ADMIN_DASHBOARD,
                self::USERS_VIEW,
                self::USERS_ASSIGN_ROLES,
                self::ROLES_VIEW,
                self::ROLES_MANAGE,
                self::PERMISSIONS_VIEW,
                self::PERMISSIONS_MANAGE,
            ],
            'Opportunities' => [
                self::OPPORTUNITIES_VIEW,
                self::OPPORTUNITIES_CREATE,
                self::OPPORTUNITIES_UPDATE,
                self::OPPORTUNITIES_SUBMIT,
                self::OPPORTUNITIES_APPROVE,
                self::OPPORTUNITIES_REJECT,
                self::OPPORTUNITIES_REASSIGN,
                self::OPPORTUNITIES_ACTIVATE,
                self::OPPORTUNITIES_COMPLETE,
                self::OPPORTUNITIES_CANCEL,
                self::CATALOGUE_MANAGE,
            ],
            'Engagement' => [
                self::ENGAGEMENT_VIEW,
                self::ENGAGEMENT_REVIEW,
                self::PROFILES_REVIEW,
            ],
            'Contact' => [
                self::CONTACT_VIEW,
                self::CONTACT_MANAGE,
            ],
            'Audit' => [
                self::AUDIT_VIEW,
            ],
            'Messaging' => [
                self::SMS_MANAGE,
                self::SMS_MESSAGES_VIEW,
                self::SMS_CAMPAIGNS_MANAGE,
            ],
            'Assistant' => [
                self::ASSISTANT_KNOWLEDGE_VIEW,
                self::ASSISTANT_KNOWLEDGE_MANAGE,
            ],
            'Operations' => [
                self::BACKUPS_VIEW,
                self::BACKUPS_MANAGE,
                self::BACKUPS_DOWNLOAD,
            ],
            'Investor' => [
                self::INVESTOR_DASHBOARD,
                self::INVESTOR_OPPORTUNITIES,
                self::INVESTOR_ENGAGEMENT,
            ],
        ];
    }
}
