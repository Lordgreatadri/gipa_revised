The audit log was already in place and remains on the staff dashboard. Login history is now a separate record of sign-in attempts, visible to the Super Administrator.

**Audit log.** Material actions, including successful sign-in, failed sign-in, lockout, logout, e-mail verification, and password reset, are already written to the append-only audit log with the IP address, browser, and request id. That page is under Assurance and on the dashboard for anyone with `audit.view`: the Super Administrator and the Reviewer / Approver. Credentials are not stored there.

**Login history.** Each website and API sign-in, failed attempt, and lockout is also stored in `login_attempts`. A row keeps the account, result, guard, IP address, browser, remember-me flag, and location when the address is public. Private addresses such as `127.0.0.1` are stored without a location. A lookup failure does not block sign-in. The password is never written.

Location comes from [stevebauman/location](https://github.com/stevebauman/location) 7.6, using ip-api.com for public addresses only. A local MaxMind database can replace that driver later. The keys are commented in `.env.example`.

There is no separate Administrator role. The Super Administrator receives `security.logins.view` with the rest of the catalogue. The dashboard shows a recent-attempts table, and Assurance has a Login history page with account and result filters. Any other role can be given the same permission from the role form, under Audit. Reviewers, content managers, district officers, and investors do not receive it by default.

The `login_attempts` table is migrated, and the permission catalogue was seeded so the existing Super Administrator can open the page. The browser session is signed out on the login screen, so the page itself was checked through the feature test, which rendered the history, the Accra location, and the address.

`LoginHistoryTest` passed (6 tests), along with the existing login and admin dashboard tests.

**Changed files**

- `app/Modules/Identity/Application/Listeners/RecordAuthenticationAudit.php`
- `app/Modules/Identity/Application/LoginHistory/LoginPlace.php`
- `app/Modules/Identity/Application/LoginHistory/RecordLoginAttempt.php`
- `app/Modules/Identity/Application/StaffPortfolio.php`
- `app/Modules/Identity/Domain/Access/AccessPermissions.php`
- `app/Modules/Identity/Domain/Models/LoginAttempt.php`
- `app/Modules/Identity/Infrastructure/LoginHistory/IpApiLoginPlaceLookup.php`
- `app/Modules/Identity/Presentation/Http/Controllers/LoginHistoryController.php`
- `app/Modules/Identity/Presentation/Navigation/AdminMenu.php`
- `app/Modules/Shared/Presentation/Http/Controllers/Api/V1/AuthTokenController.php`
- `config/location.php`
- `database/migrations/2026_10_19_090000_create_login_attempts_table.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/logins/index.blade.php`
- `routes/admin.php`
- `tests/Feature/Modules/Identity/LoginHistoryTest.php`
- `.env.example`
- `composer.json`
- `composer.lock`