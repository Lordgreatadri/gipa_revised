<?php

namespace App\Modules\Identity\Domain;

/**
 * Stable audit action identifiers owned by Identity & Access.
 */
final class IdentityAuditActions
{
    public const LOGIN_SUCCEEDED = 'auth.login_succeeded';

    public const LOGIN_FAILED = 'auth.login_failed';

    public const LOGIN_LOCKED_OUT = 'auth.login_locked_out';

    public const LOGOUT = 'auth.logout';

    public const EMAIL_VERIFIED = 'account.email_verified';

    public const EMAIL_VERIFICATION_RESENT = 'account.email_verification_resent';

    public const PASSWORD_RESET_REQUESTED = 'account.password_reset_requested';

    public const PASSWORD_RESET = 'account.password_reset';

    public const ROLE_CREATED = 'access.role_created';

    public const ROLE_UPDATED = 'access.role_updated';

    public const ROLE_DELETED = 'access.role_deleted';

    public const PERMISSION_CREATED = 'access.permission_created';

    public const PERMISSION_DELETED = 'access.permission_deleted';

    public const USER_ROLES_ASSIGNED = 'access.user_roles_assigned';

    public const STAFF_DISTRICTS_ASSIGNED = 'access.staff_districts_assigned';

    public const STAFF_INVITED = 'access.staff_invited';
}
