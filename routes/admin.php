<?php

use App\Modules\Assistant\Presentation\Http\Controllers\AssistantKnowledgeController;
use App\Modules\Backup\Presentation\Http\Controllers\BackupController;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Presentation\Http\Controllers\AdminDashboardController;
use App\Modules\Identity\Presentation\Http\Controllers\ReportingController;
use App\Modules\InvestorEngagement\Presentation\Http\Controllers\EngagementReviewController;
use App\Modules\Geography\Presentation\Http\Controllers\GeographyController;
use App\Modules\Opportunity\Presentation\Http\Controllers\CatalogueController;
use App\Modules\Opportunity\Presentation\Http\Controllers\OpportunityController;
use App\Modules\Audit\Presentation\Http\Controllers\AuditLogController;
use App\Modules\Shared\Presentation\Http\Controllers\SmsCampaignController;
use App\Modules\Shared\Presentation\Http\Controllers\SmsMessageController;
use App\Modules\Shared\Presentation\Http\Controllers\SmsSettingsController;
use App\Modules\Identity\Presentation\Http\Controllers\PermissionController;
use App\Modules\Identity\Presentation\Http\Controllers\RoleController;
use App\Modules\Identity\Presentation\Http\Controllers\StaffUserController;
use App\Modules\InvestorOnboarding\Presentation\Http\Controllers\InvestorProfileReviewController;
use App\Modules\PublicPortal\Presentation\Http\Controllers\ContactInboxController;
use App\Modules\Shared\Presentation\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'staff', 'can:'.AccessPermissions::ADMIN_DASHBOARD])
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/reports', ReportingController::class)->name('reports');

        Route::middleware('can:'.AccessPermissions::USERS_VIEW)->group(function (): void {
            Route::get('/users', [StaffUserController::class, 'index'])->name('users.index');
            Route::get('/users/export', [StaffUserController::class, 'export'])->name('users.export');
            Route::post('/users/invite', [StaffUserController::class, 'invite'])
                ->middleware('can:'.AccessPermissions::USERS_ASSIGN_ROLES)
                ->name('users.invite');
            Route::get('/users/{user}', [StaffUserController::class, 'edit'])
                ->middleware('can:'.AccessPermissions::USERS_ASSIGN_ROLES)
                ->name('users.edit');
            Route::put('/users/{user}', [StaffUserController::class, 'update'])
                ->middleware('can:'.AccessPermissions::USERS_ASSIGN_ROLES)
                ->name('users.update');
        });

        Route::middleware('can:'.AccessPermissions::ROLES_VIEW)->group(function (): void {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/roles/create', [RoleController::class, 'create'])
                ->middleware('can:'.AccessPermissions::ROLES_MANAGE)
                ->name('roles.create');
            Route::post('/roles', [RoleController::class, 'store'])
                ->middleware('can:'.AccessPermissions::ROLES_MANAGE)
                ->name('roles.store');
            Route::get('/roles/{role}', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [RoleController::class, 'update'])
                ->middleware('can:'.AccessPermissions::ROLES_MANAGE)
                ->name('roles.update');
            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
                ->middleware('can:'.AccessPermissions::ROLES_MANAGE)
                ->name('roles.destroy');
        });

        Route::middleware('can:'.AccessPermissions::PERMISSIONS_VIEW)->group(function (): void {
            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::post('/permissions', [PermissionController::class, 'store'])
                ->middleware('can:'.AccessPermissions::PERMISSIONS_MANAGE)
                ->name('permissions.store');
            Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
                ->middleware('can:'.AccessPermissions::PERMISSIONS_MANAGE)
                ->name('permissions.destroy');
        });

        Route::middleware('can:'.AccessPermissions::OPPORTUNITIES_VIEW)->group(function (): void {
            Route::get('/regions', [GeographyController::class, 'regions'])->name('regions.index');
            Route::get('/districts', [GeographyController::class, 'districts'])->name('districts.index');
            Route::get('/districts/register', [GeographyController::class, 'districtRegister'])->name('districts.register');
        });

        Route::middleware('can:'.AccessPermissions::CATALOGUE_MANAGE)->group(function (): void {
            Route::get('/catalogue', [CatalogueController::class, 'index'])->name('catalogue.index');
            Route::get('/sectors', [CatalogueController::class, 'sectors'])->name('sectors.index');
            Route::get('/sectors/register', [CatalogueController::class, 'sectorRegister'])->name('sectors.register');
            Route::get('/sub-sectors', [CatalogueController::class, 'subSectors'])->name('sub-sectors.index');
            Route::get('/sub-sectors/register', [CatalogueController::class, 'subSectorRegister'])->name('sub-sectors.register');
            Route::get('/enterprise-types', [CatalogueController::class, 'enterpriseTypes'])->name('types.index');
            Route::get('/enterprise-types/register', [CatalogueController::class, 'enterpriseTypeRegister'])->name('types.register');
            Route::post('/catalogue/sectors', [CatalogueController::class, 'storeSector'])->name('catalogue.sectors.store');
            Route::put('/catalogue/sectors/{sector}', [CatalogueController::class, 'updateSector'])->name('catalogue.sectors.update');
            Route::delete('/catalogue/sectors/{sector}', [CatalogueController::class, 'destroySector'])->name('catalogue.sectors.destroy');
            Route::post('/catalogue/sub-sectors', [CatalogueController::class, 'storeSubSector'])->name('catalogue.sub-sectors.store');
            Route::put('/catalogue/sub-sectors/{subSector}', [CatalogueController::class, 'updateSubSector'])->name('catalogue.sub-sectors.update');
            Route::delete('/catalogue/sub-sectors/{subSector}', [CatalogueController::class, 'destroySubSector'])->name('catalogue.sub-sectors.destroy');
            Route::post('/catalogue/enterprise-types', [CatalogueController::class, 'storeEnterpriseType'])->name('catalogue.types.store');
            Route::put('/catalogue/enterprise-types/{enterpriseType}', [CatalogueController::class, 'updateEnterpriseType'])->name('catalogue.types.update');
            Route::delete('/catalogue/enterprise-types/{enterpriseType}', [CatalogueController::class, 'destroyEnterpriseType'])->name('catalogue.types.destroy');
        });

        Route::middleware('can:'.AccessPermissions::OPPORTUNITIES_VIEW)->group(function (): void {
            Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
            Route::get('/opportunities/register', [OpportunityController::class, 'register'])->name('opportunities.register');
            Route::get('/opportunities/create', [OpportunityController::class, 'create'])
                ->middleware('can:'.AccessPermissions::OPPORTUNITIES_CREATE)
                ->name('opportunities.create');
            Route::post('/opportunities', [OpportunityController::class, 'store'])
                ->middleware('can:'.AccessPermissions::OPPORTUNITIES_CREATE)
                ->name('opportunities.store');
            Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show'])->name('opportunities.show');
            Route::get('/opportunities/{opportunity}/edit', [OpportunityController::class, 'edit'])
                ->middleware('can:'.AccessPermissions::OPPORTUNITIES_UPDATE)
                ->name('opportunities.edit');
            Route::put('/opportunities/{opportunity}', [OpportunityController::class, 'update'])
                ->middleware('can:'.AccessPermissions::OPPORTUNITIES_UPDATE)
                ->name('opportunities.update');
            Route::post('/opportunities/{opportunity}/transitions', [OpportunityController::class, 'transition'])
                ->name('opportunities.transition');
            Route::post('/opportunities/{opportunity}/documents', [OpportunityController::class, 'storeDocument'])
                ->name('opportunities.documents.store');
            Route::get('/opportunities/{opportunity}/documents/{document}', [OpportunityController::class, 'download'])
                ->name('opportunities.documents.download');
        });
        Route::get('/engagement', [EngagementReviewController::class, 'index'])
            ->middleware('can:'.AccessPermissions::ENGAGEMENT_VIEW)
            ->name('engagement');
        Route::get('/engagement/{engagement}', [EngagementReviewController::class, 'show'])
            ->middleware('can:'.AccessPermissions::ENGAGEMENT_VIEW)
            ->name('engagement.show');
        Route::post('/engagement/{engagement}/messages', [EngagementReviewController::class, 'message'])
            ->middleware('can:'.AccessPermissions::ENGAGEMENT_REVIEW)
            ->name('engagement.messages.store');
        Route::middleware('can:'.AccessPermissions::PROFILES_REVIEW)->group(function (): void {
            Route::get('/investor-profiles', [InvestorProfileReviewController::class, 'index'])->name('profiles.index');
            Route::post('/investor-profiles/{profile}/approve', [InvestorProfileReviewController::class, 'approve'])->name('profiles.approve');
        });
        Route::middleware('can:'.AccessPermissions::CONTACT_VIEW)->group(function (): void {
            Route::get('/contact', [ContactInboxController::class, 'index'])->name('contact');
            Route::get('/contact/{enquiry}', [ContactInboxController::class, 'show'])->name('contact.show');
            Route::post('/contact/{enquiry}/reply', [ContactInboxController::class, 'reply'])
                ->middleware('can:'.AccessPermissions::CONTACT_MANAGE)
                ->name('contact.reply');
            Route::post('/contact/{enquiry}/close', [ContactInboxController::class, 'close'])
                ->middleware('can:'.AccessPermissions::CONTACT_MANAGE)
                ->name('contact.close');
        });
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::middleware('can:'.AccessPermissions::AUDIT_VIEW)->group(function (): void {
            Route::get('/audit', [AuditLogController::class, 'index'])->name('audit');
            Route::get('/audit/export', [AuditLogController::class, 'export'])->name('audit.export');
        });
        Route::middleware('can:'.AccessPermissions::SMS_MESSAGES_VIEW)->group(function (): void {
            Route::get('/sms/messages', [SmsMessageController::class, 'index'])->name('sms.messages');
            Route::get('/sms/messages/{message}', [SmsMessageController::class, 'show'])->name('sms.messages.show');
            Route::post('/sms/messages/{message}/resend', [SmsMessageController::class, 'resend'])->name('sms.messages.resend');
        });
        Route::middleware('can:'.AccessPermissions::SMS_CAMPAIGNS_MANAGE)->group(function (): void {
            Route::get('/sms/campaigns', [SmsCampaignController::class, 'index'])->name('sms.campaigns');
            Route::get('/sms/campaigns/create', [SmsCampaignController::class, 'create'])->name('sms.campaigns.create');
            Route::post('/sms/campaigns', [SmsCampaignController::class, 'store'])->name('sms.campaigns.store');
            Route::get('/sms/campaigns/{campaign}', [SmsCampaignController::class, 'show'])->name('sms.campaigns.show');
            Route::post('/sms/campaigns/{campaign}/send', [SmsCampaignController::class, 'send'])->name('sms.campaigns.send');
        });
        Route::middleware('can:'.AccessPermissions::ASSISTANT_KNOWLEDGE_VIEW)->group(function (): void {
            Route::get('/assistant/knowledge', [AssistantKnowledgeController::class, 'index'])->name('assistant.knowledge.index');
        });
        Route::middleware('can:'.AccessPermissions::ASSISTANT_KNOWLEDGE_MANAGE)->group(function (): void {
            Route::get('/assistant/knowledge/create', [AssistantKnowledgeController::class, 'create'])->name('assistant.knowledge.create');
            Route::post('/assistant/knowledge', [AssistantKnowledgeController::class, 'store'])->name('assistant.knowledge.store');
            Route::post('/assistant/knowledge/reindex', [AssistantKnowledgeController::class, 'reindex'])->name('assistant.knowledge.reindex');
            Route::get('/assistant/knowledge/{document}', [AssistantKnowledgeController::class, 'edit'])->name('assistant.knowledge.edit');
            Route::put('/assistant/knowledge/{document}', [AssistantKnowledgeController::class, 'update'])->name('assistant.knowledge.update');
            Route::delete('/assistant/knowledge/{document}', [AssistantKnowledgeController::class, 'destroy'])->name('assistant.knowledge.destroy');
        });
        Route::middleware('can:'.AccessPermissions::BACKUPS_VIEW)->group(function (): void {
            Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        });
        Route::middleware('can:'.AccessPermissions::BACKUPS_MANAGE)->group(function (): void {
            Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
            Route::post('/backups/retention', [BackupController::class, 'retention'])->name('backups.retention');
            Route::post('/backups/inspect', [BackupController::class, 'inspect'])->name('backups.inspect');
            Route::delete('/backups/{archive}', [BackupController::class, 'destroy'])->name('backups.destroy');
        });
        Route::middleware('can:'.AccessPermissions::BACKUPS_DOWNLOAD)->group(function (): void {
            Route::get('/backups/{archive}', [BackupController::class, 'download'])->name('backups.download');
        });
        Route::middleware('can:'.AccessPermissions::SMS_MANAGE)->group(function (): void {
            Route::get('/sms', [SmsSettingsController::class, 'index'])->name('sms.index');
            Route::get('/sms/providers/{provider}', [SmsSettingsController::class, 'edit'])->name('sms.edit');
            Route::put('/sms/providers/{provider}', [SmsSettingsController::class, 'update'])->name('sms.update');
            Route::post('/sms/providers/{provider}/switch', [SmsSettingsController::class, 'switch'])->name('sms.switch');
            Route::post('/sms/providers/{provider}/toggle', [SmsSettingsController::class, 'toggle'])->name('sms.toggle');
            Route::post('/sms/providers/{provider}/balance', [SmsSettingsController::class, 'balance'])->name('sms.balance');
            Route::post('/sms/providers/{provider}/test', [SmsSettingsController::class, 'test'])->name('sms.test');
        });
    });
