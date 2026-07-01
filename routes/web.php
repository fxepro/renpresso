<?php
use App\Http\Controllers\Auth\{LoginController, RegisterController, ForgotPasswordController, MaintenanceStaffRegisterController, CleaningStaffRegisterController};
use App\Http\Controllers\{WaitlistController, WebhookController, DashboardController, ApplicationController,
    PropertyController, PropertyMediaController, PropertyUnitSlotController, LeaseController, SubLeaseController, TenantController, TenantPortalController, PaymentController, FxLedgerController, TaxExportController, MaintenanceController, PublicListingController,
    LandlordAccountController, LandlordMaintenanceTeamController, LandlordCleaningTeamController, DocumentFileController, MessageController, DocumentsController, LeaseTemplateController, HelpController, DealsController,
    MaintenancePortalController, MaintenanceCityController, MaintenanceTeamProfileController, MaintenancePaymentsController, MaintenanceInvoiceController, MaintenanceAccountController,
    CleaningPortalController, CleaningCityController, CleaningTeamProfileController, CleaningAccountController,
    LandlordMaintenanceInvoiceController, LandlordCommunicationController,
    Admin\AdminController, Admin\AdminSettingsController, Admin\AdminMessagesController};
use Illuminate\Support\Facades\Route;

// ── Marketing ──
Route::get('/',             fn() => view('pages.index'))->name('home');
Route::get('/how-it-works', fn() => view('pages.how-it-works'))->name('how-it-works');
Route::get('/features',     fn() => view('pages.features'))->name('features');
Route::get('/rental-types', fn() => view('pages.rental-types'))->name('rental-types');
Route::get('/pricing',      fn() => view('pages.pricing'))->name('pricing');
Route::get('/countries',    fn() => view('pages.countries'))->name('countries');
Route::get('/about',        fn() => view('pages.about'))->name('about');
Route::get('/blog',         fn() => view('pages.blog'))->name('blog');
Route::get('/careers',      fn() => view('pages.careers'))->name('careers');
Route::get('/security',     fn() => view('pages.security'))->name('security');
Route::get('/contact',      fn() => view('pages.contact'))->name('contact');
Route::get('/privacy',      fn() => view('pages.privacy'))->name('privacy');
Route::get('/terms',        fn() => view('pages.terms'))->name('terms');
Route::get('/cookies',      fn() => view('pages.cookies'))->name('cookies');
Route::get('/waitlist',     fn() => view('pages.waitlist'))->name('waitlist');
Route::post('/waitlist',    [WaitlistController::class, 'store'])->name('waitlist.store');

// ── Public listings (guest) ──
Route::get('/listings', [PublicListingController::class, 'index'])->name('listings.index');
// Legacy redirects — keep old URLs working
Route::get('/listings/long-term',  fn () => redirect()->route('listings.index', ['tab' => 'long-term'],  301))->name('listings.long-term');
Route::get('/listings/short-term', fn () => redirect()->route('listings.index', ['tab' => 'short-term'], 301))->name('listings.short-term');
Route::get('/listings/sublets',    fn () => redirect()->route('listings.index', ['tab' => 'sublets'],    301))->name('listings.sublets');
Route::get('/listings/roommates',  fn () => redirect()->route('listings.index', ['tab' => 'roommates'],  301))->name('listings.roommates');
Route::get('/listings/long-term/{property}', [PublicListingController::class, 'showLongTerm'])
    ->name('listings.long-term.show')
    ->whereUuid('property');
Route::get('/listings/short-term/{property}', [PublicListingController::class, 'showShortTerm'])
    ->name('listings.short-term.show')
    ->whereUuid('property');

// ── Auth ──
Route::get('/login',    [LoginController::class, 'show'])->name('login');
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('auth.register');

Route::middleware('guest')->group(function () {
    Route::post('/login',           [LoginController::class, 'login'])->name('auth.login');
    Route::get('/register/maintenance/{invite_token?}', [MaintenanceStaffRegisterController::class, 'show'])->name('register.maintenance');
    Route::post('/register/maintenance', [MaintenanceStaffRegisterController::class, 'store'])->name('register.maintenance.store');
    Route::get('/register/cleaning/{invite_token?}', [CleaningStaffRegisterController::class, 'show'])->name('register.cleaning');
    Route::post('/register/cleaning', [CleaningStaffRegisterController::class, 'store'])->name('register.cleaning.store');
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout')->middleware('auth');

// ── Webhooks ──
Route::post('/webhooks/{processor}', [WebhookController::class, 'handle'])
    ->name('webhooks.handle')
    ->whereIn('processor', ['stripe','razorpay','flutterwave','xendit','mercadopago']);

// ── Dashboard ──
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->isMaintenance()) {
            return redirect()->route('maint.dashboard');
        }
        if (auth()->user()->isCleaning()) {
            return redirect()->route('clean.dashboard');
        }
        if (auth()->user()->isTenant()) {
            return app(TenantPortalController::class)->index();
        }

        return view('dashboard.index');
    })->name('dashboard');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
            Route::get('/general', [AdminSettingsController::class, 'general'])->name('general');
            Route::put('/general', [AdminSettingsController::class, 'updateGeneral'])->name('general.update');
            Route::get('/payments', [AdminSettingsController::class, 'payments'])->name('payments');
            Route::put('/payments/choices', [AdminSettingsController::class, 'updatePaymentChoices'])->name('payments.choices.update');
            Route::patch('/payments/{provider}', [AdminSettingsController::class, 'updatePaymentProvider'])->name('payments.update');
            Route::get('/markets', [AdminSettingsController::class, 'markets'])->name('markets');
            Route::post('/markets/apply-defaults', [AdminSettingsController::class, 'applyDefaultsToMarkets'])->name('markets.apply-defaults');
            Route::patch('/markets/{market}', [AdminSettingsController::class, 'updateMarket'])->name('markets.update');
        });

        Route::get('/tenants', [AdminController::class, 'tenants'])->name('tenants');
        Route::get('/tenants/{user}', [AdminController::class, 'tenantShow'])->name('tenants.show');

        Route::get('/maintenance-teams', [AdminController::class, 'maintenanceTeams'])->name('maintenance-teams');
        Route::get('/maintenance-teams/{maintenanceTeam}', [AdminController::class, 'maintenanceTeamShow'])->name('maintenance-teams.show');

        Route::get('/maintenance-requests', [AdminController::class, 'maintenanceRequests'])->name('maintenance-requests');
        Route::get('/maintenance-requests/{maintenanceRequest}', [AdminController::class, 'maintenanceRequestShow'])->name('maintenance-requests.show');

        Route::get('/maintenance-invoices', [AdminController::class, 'maintenanceInvoices'])->name('maintenance-invoices');
        Route::get('/maintenance-invoices/{maintenanceInvoice}', [AdminController::class, 'maintenanceInvoiceShow'])->name('maintenance-invoices.show');

        Route::get('/properties', [AdminController::class, 'properties'])->name('properties');
        Route::get('/properties/{property}', [AdminController::class, 'propertyShow'])->name('properties.show');

        Route::get('/leases', [AdminController::class, 'leases'])->name('leases');
        Route::get('/leases/{lease}', [AdminController::class, 'leaseShow'])->name('leases.show');

        Route::get('/landlords', [AdminController::class, 'landlords'])->name('landlords');
        Route::get('/landlords/{user}', [AdminController::class, 'landlordShow'])->name('landlords.show');

        Route::get('/landlord-billing', [AdminController::class, 'landlordBillingIndex'])->name('landlord-billing');
        Route::get('/landlord-billing/{year}/{month}', [AdminController::class, 'landlordBillingMonth'])
            ->name('landlord-billing.month')
            ->whereNumber(['year', 'month']);

        Route::get('/revenue', [AdminController::class, 'revenue'])->name('revenue');

        Route::get('/messages', [AdminMessagesController::class, 'index'])->name('messages');
        Route::get('/messages/create', [AdminMessagesController::class, 'create'])->name('messages.create');
        Route::post('/messages', [AdminMessagesController::class, 'store'])->name('messages.store');
        Route::get('/messages/{emailTemplate}', [AdminMessagesController::class, 'show'])->name('messages.show');
        Route::get('/messages/{emailTemplate}/edit', [AdminMessagesController::class, 'edit'])->name('messages.edit');
        Route::put('/messages/{emailTemplate}', [AdminMessagesController::class, 'update'])->name('messages.update');
        Route::patch('/messages/{emailTemplate}/publish', [AdminMessagesController::class, 'togglePublish'])->name('messages.publish');
        Route::delete('/messages/{emailTemplate}', [AdminMessagesController::class, 'destroy'])->name('messages.destroy');

        Route::get('/rent-collections', [AdminController::class, 'rentCollections'])->name('rent-collections');
        Route::get('/rent-collections/{year}/{month}', [AdminController::class, 'rentCollectionsMonth'])
            ->name('rent-collections.month')
            ->whereNumber(['year', 'month']);

        Route::get('/{page}', [AdminController::class, 'placeholder'])
            ->name('page')
            ->where('page', config('admin.pages'));
    });

    Route::prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/home', [TenantPortalController::class, 'home'])->name('home');
        Route::get('/payments', [TenantPortalController::class, 'payments'])->name('payments');
        Route::post('/payments/complete', [TenantPortalController::class, 'completePayment'])->name('payments.complete');
        Route::get('/account-ledger', [TenantPortalController::class, 'accountLedger'])->name('account-ledger');
        Route::redirect('/payment-ledger', '/tenant/account-ledger');
        Route::get('/account', [TenantPortalController::class, 'account'])->name('account');
        Route::post('/account/profile', [TenantPortalController::class, 'updateProfile'])->name('account.profile');
        Route::get('/account/id-document', [TenantPortalController::class, 'idDocument'])->name('account.id-document');
        Route::post('/account/background-check', [TenantPortalController::class, 'storeBackgroundCheck'])->name('account.background-check');
        Route::post('/account/payment-methods', [TenantPortalController::class, 'storePaymentMethod'])->name('account.payment-methods.store');
        Route::put('/account/payment-methods/{method}', [TenantPortalController::class, 'updatePaymentMethod'])->name('account.payment-methods.update');
        Route::delete('/account/payment-methods/{method}', [TenantPortalController::class, 'destroyPaymentMethod'])->name('account.payment-methods.destroy');
        Route::patch('/account/payment-methods/{method}/default', [TenantPortalController::class, 'defaultPaymentMethod'])->name('account.payment-methods.default');
    });
    Route::get('/landlord/kyc', fn () => redirect()->route('landlord.account'))->name('landlord.kyc');
    Route::get('/landlord/account', [LandlordAccountController::class, 'show'])->name('landlord.account');
    Route::post('/landlord/account/identity', [LandlordAccountController::class, 'updateIdentity'])->name('landlord.account.identity');
    Route::post('/landlord/account/business', [LandlordAccountController::class, 'updateBusiness'])->name('landlord.account.business');
    Route::post('/landlord/account/portfolio-activate', [LandlordAccountController::class, 'activatePortfolio'])->name('landlord.account.portfolio-activate');
    Route::post('/landlord/account/portfolio-defaults', [LandlordAccountController::class, 'updatePortfolioDefaults'])->name('landlord.account.portfolio-defaults');
    Route::post('/landlord/account/payment-methods', [LandlordAccountController::class, 'storePaymentMethod'])->name('landlord.account.payment-methods.store');
    Route::put('/landlord/account/payment-methods/{method}', [LandlordAccountController::class, 'updatePaymentMethod'])->name('landlord.account.payment-methods.update');
    Route::patch('/landlord/account/payment-methods/{method}/default', [LandlordAccountController::class, 'defaultPaymentMethod'])->name('landlord.account.payment-methods.default');
    Route::delete('/landlord/account/payment-methods/{method}', [LandlordAccountController::class, 'destroyPaymentMethod'])->name('landlord.account.payment-methods.destroy');
    Route::post('/landlord/account/payout-accounts', [LandlordAccountController::class, 'storePayoutAccount'])->name('landlord.account.payout-accounts.store');
    Route::delete('/landlord/account/payout-accounts/{payoutAccount}', [LandlordAccountController::class, 'destroyPayoutAccount'])->name('landlord.account.payout-accounts.destroy');

    Route::get('/landlord/maintenance-team', [LandlordMaintenanceTeamController::class, 'index'])->name('landlord.maintenance-team.index');
    Route::get('/landlord/maintenance-team/{team}', [LandlordMaintenanceTeamController::class, 'show'])->name('landlord.maintenance-team.show');
    Route::post('/landlord/maintenance-team/{team}/review', [LandlordMaintenanceTeamController::class, 'storeReview'])->name('landlord.maintenance-team.review');
    Route::post('/landlord/maintenance-team/invite', [LandlordMaintenanceTeamController::class, 'storeInvite'])->name('landlord.maintenance-team.invite');
    Route::post('/landlord/maintenance-team/{team}/engage', [LandlordMaintenanceTeamController::class, 'engage'])->name('landlord.maintenance-team.engage');
    Route::delete('/landlord/maintenance-team/{team}', [LandlordMaintenanceTeamController::class, 'disengage'])->name('landlord.maintenance-team.disengage');

    Route::get('/landlord/cleaning-team', [LandlordCleaningTeamController::class, 'index'])->name('landlord.cleaning-team.index');
    Route::get('/landlord/cleaning-team/{team}', [LandlordCleaningTeamController::class, 'show'])->name('landlord.cleaning-team.show');
    Route::post('/landlord/cleaning-team/{team}/review', [LandlordCleaningTeamController::class, 'storeReview'])->name('landlord.cleaning-team.review');
    Route::post('/landlord/cleaning-team/invite', [LandlordCleaningTeamController::class, 'storeInvite'])->name('landlord.cleaning-team.invite');
    Route::post('/landlord/cleaning-team/{team}/engage', [LandlordCleaningTeamController::class, 'engage'])->name('landlord.cleaning-team.engage');
    Route::delete('/landlord/cleaning-team/{team}', [LandlordCleaningTeamController::class, 'disengage'])->name('landlord.cleaning-team.disengage');

    Route::get('/billing',              [\App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/{year}/{month}', [\App\Http\Controllers\BillingController::class, 'show'])->name('billing.show')->whereNumber(['year', 'month']);

    Route::get('/fx-ledger', [FxLedgerController::class, 'index'])->name('fx-ledger.index');
    Route::post('/fx-ledger/repatriation', [FxLedgerController::class, 'storeRepatriation'])->name('fx-ledger.repatriation.store');

    Route::get('/tax-export', [TaxExportController::class, 'index'])->name('tax-export.index');
    Route::get('/tax-export/{property}/csv', [TaxExportController::class, 'csv'])->name('tax-export.csv')->whereUuid('property');
    Route::get('/tax-export/{property}/pdf', [TaxExportController::class, 'pdf'])->name('tax-export.pdf')->whereUuid('property');

    Route::resource('properties', PropertyController::class)->only(['index','create','store','show','update','destroy']);
    Route::get('/properties/{property}/units/{unit_seq}', [PropertyController::class, 'showUnit'])->name('properties.units.show')->whereNumber('unit_seq');
    Route::post('/properties/{property}/media/photos', [PropertyMediaController::class, 'storePhoto'])->name('properties.media.photos.store');
    Route::post('/properties/{property}/media/videos', [PropertyMediaController::class, 'storeVideo'])->name('properties.media.videos.store');
    Route::delete('/properties/{property}/media/{media}', [PropertyMediaController::class, 'destroy'])->name('properties.media.destroy');
    Route::patch('/properties/{property}/rent', [PropertyController::class, 'updateRent'])->name('properties.rent.update');
    Route::patch('/properties/{property}/units/{unit_seq}', [PropertyUnitSlotController::class, 'update'])->name('properties.units.update')->whereNumber('unit_seq');
    Route::patch('/sub-leases/{subLease}/approve', [SubLeaseController::class, 'approve'])->name('sub-leases.approve');
    Route::patch('/sub-leases/{subLease}/reject', [SubLeaseController::class, 'reject'])->name('sub-leases.reject');
    Route::get('/properties/{property}/leases/create', [LeaseController::class, 'create'])->name('leases.create');
    Route::post('/properties/{property}/leases',       [LeaseController::class, 'store'])->name('leases.store');
    Route::get('/leases',          [LeaseController::class,       'index'])->name('leases.index');
    Route::get('/leases/{lease}',  [LeaseController::class,       'show'])->name('leases.show');
    Route::get('/tenants',         [TenantController::class,      'index'])->name('tenants.index');
    Route::get('/invoices', [LandlordMaintenanceInvoiceController::class, 'index'])->name('landlord.invoices.index');
    Route::get('/invoices/{invoice}', [LandlordMaintenanceInvoiceController::class, 'show'])->name('landlord.invoices.show');
    Route::post('/invoices/{invoice}/approve', [LandlordMaintenanceInvoiceController::class, 'approve'])->name('landlord.invoices.approve');
    Route::get('/invoice-attachments/{attachment}/file', [LandlordMaintenanceInvoiceController::class, 'attachmentFile'])->name('landlord.invoices.attachments.file');

    Route::get('/payments', function (\Illuminate\Http\Request $request) {
        if (auth()->user()->isTenant()) {
            return redirect()->route('tenant.payments', $request->query());
        }

        return app(PaymentController::class)->index($request);
    })->name('payments.index');

    Route::get('/messages',                         [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/property/{property}',    [MessageController::class, 'propertyShow'])->name('messages.property');
    Route::post('/messages/property/{property}',   [MessageController::class, 'propertyStore'])->name('messages.property.store');
    Route::get('/messages/lease/{lease}',          [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/lease/{lease}',         [MessageController::class, 'store'])->name('messages.store');

    Route::get('/documents', [DocumentsController::class, 'index'])->name('documents.index');
    Route::get('/lease-templates/create', [LeaseTemplateController::class, 'create'])->name('lease-templates.create');
    Route::post('/lease-templates', [LeaseTemplateController::class, 'store'])->name('lease-templates.store');
    Route::get('/lease-templates/{leaseTemplate}/edit', [LeaseTemplateController::class, 'edit'])->name('lease-templates.edit');
    Route::put('/lease-templates/{leaseTemplate}', [LeaseTemplateController::class, 'update'])->name('lease-templates.update');
    Route::delete('/lease-templates/{leaseTemplate}', [LeaseTemplateController::class, 'destroy'])->name('lease-templates.destroy');
    Route::get('/lease-templates/{leaseTemplate}/file', [LeaseTemplateController::class, 'file'])->name('lease-templates.file');

    Route::prefix('deals')->name('deals.')->group(function () {
        Route::get('/insurance', [DealsController::class, 'insurance'])->name('insurance');
        Route::get('/coupons', [DealsController::class, 'coupons'])->name('coupons');
    });

    Route::prefix('help')->group(function () {
        Route::get('/videos', [HelpController::class, 'videos'])->name('help.videos');
        Route::get('/collateral', [HelpController::class, 'collateral'])->name('help.collateral');
        Route::get('/helpline', [HelpController::class, 'helpline'])->name('help.helpline');
        Route::post('/helpline/ask', [HelpController::class, 'helplineAsk'])->name('help.helpline.ask');
        Route::post('/helpline/feedback', [HelpController::class, 'helplineFeedback'])->name('help.helpline.feedback');
    });

    Route::middleware('maintenance')->prefix('maintenance')->name('maint.')->group(function () {
        Route::get('/dashboard', [MaintenancePortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/operations/cities', [MaintenanceCityController::class, 'index'])->name('cities.index');
        Route::post('/operations/cities', [MaintenanceCityController::class, 'store'])->name('cities.store');
        Route::put('/operations/cities/{city}', [MaintenanceCityController::class, 'update'])->name('cities.update');
        Route::delete('/operations/cities/{city}', [MaintenanceCityController::class, 'destroy'])->name('cities.destroy');
        Route::get('/operations/team', [MaintenanceTeamProfileController::class, 'edit'])->name('team.edit');
        Route::put('/operations/team', [MaintenanceTeamProfileController::class, 'update'])->name('team.update');
        Route::get('/payments/invoices', [MaintenanceInvoiceController::class, 'index'])->name('payments.invoices');
        Route::get('/payments/invoices/form-options', [MaintenanceInvoiceController::class, 'formOptions'])->name('payments.invoices.form-options');
        Route::get('/payments/invoices/create', [MaintenanceInvoiceController::class, 'create'])->name('payments.invoices.create');
        Route::post('/payments/invoices', [MaintenanceInvoiceController::class, 'store'])->name('payments.invoices.store');
        Route::get('/payments/invoices/{invoice}', [MaintenanceInvoiceController::class, 'show'])->name('payments.invoices.show');
        Route::get('/payments/invoices/{invoice}/edit', [MaintenanceInvoiceController::class, 'edit'])->name('payments.invoices.edit');
        Route::put('/payments/invoices/{invoice}', [MaintenanceInvoiceController::class, 'update'])->name('payments.invoices.update');
        Route::delete('/payments/invoices/{invoice}', [MaintenanceInvoiceController::class, 'destroy'])->name('payments.invoices.destroy');
        Route::post('/payments/invoices/{invoice}/send', [MaintenanceInvoiceController::class, 'send'])->name('payments.invoices.send');
        Route::post('/payments/invoices/{invoice}/cancel', [MaintenanceInvoiceController::class, 'cancel'])->name('payments.invoices.cancel');
        Route::post('/payments/invoices/{invoice}/attachments', [MaintenanceInvoiceController::class, 'storeAttachment'])->name('payments.invoices.attachments.store');
        Route::get('/payments/invoices/attachments/{attachment}/file', [MaintenanceInvoiceController::class, 'attachmentFile'])->name('payments.invoices.attachments.file');
        Route::delete('/payments/invoices/attachments/{attachment}', [MaintenanceInvoiceController::class, 'destroyAttachment'])->name('payments.invoices.attachments.destroy');
        Route::get('/payments', [MaintenancePaymentsController::class, 'index'])->name('payments');
        Route::post('/payments/invoices/{invoice}/pay', [MaintenancePaymentsController::class, 'storeForInvoice'])->name('payments.invoices.pay');
        Route::delete('/payments/{payment}', [MaintenancePaymentsController::class, 'destroy'])->name('payments.destroy');
        Route::get('/account', [MaintenanceAccountController::class, 'show'])->name('account');
        Route::put('/account/profile', [MaintenanceAccountController::class, 'updateProfile'])->name('account.profile');
        Route::put('/account/team', [MaintenanceAccountController::class, 'updateTeam'])->name('account.team');
        Route::put('/account/password', [MaintenanceAccountController::class, 'updatePassword'])->name('account.password');
        Route::post('/account/director-identity', [MaintenanceAccountController::class, 'updateDirectorIdentity'])->name('account.director-identity');
        Route::get('/account/director-id', [MaintenanceAccountController::class, 'directorIdDocument'])->name('account.director-id');
        Route::post('/account/documents/{documentType}', [MaintenanceAccountController::class, 'storeDocument'])->name('account.documents.store');
        Route::get('/account/documents/{document}/file', [MaintenanceAccountController::class, 'documentFile'])->name('account.documents.file');
        Route::delete('/account/documents/{document}', [MaintenanceAccountController::class, 'destroyDocument'])->name('account.documents.destroy');
    });

    Route::middleware('cleaning')->prefix('cleaning')->name('clean.')->group(function () {
        Route::get('/dashboard', [CleaningPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/operations/cities', [CleaningCityController::class, 'index'])->name('cities.index');
        Route::post('/operations/cities', [CleaningCityController::class, 'store'])->name('cities.store');
        Route::put('/operations/cities/{city}', [CleaningCityController::class, 'update'])->name('cities.update');
        Route::delete('/operations/cities/{city}', [CleaningCityController::class, 'destroy'])->name('cities.destroy');
        Route::get('/operations/team', [CleaningTeamProfileController::class, 'edit'])->name('team.edit');
        Route::put('/operations/team', [CleaningTeamProfileController::class, 'update'])->name('team.update');
        Route::get('/account', [CleaningAccountController::class, 'show'])->name('account');
        Route::put('/account/profile', [CleaningAccountController::class, 'updateProfile'])->name('account.profile');
        Route::put('/account/password', [CleaningAccountController::class, 'updatePassword'])->name('account.password');
    });

    Route::prefix('maintenance-requests')->group(function () {
        Route::get('/', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::get('/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
        Route::post('/', [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::get('/{maintenanceRequest}', [MaintenanceController::class, 'show'])->name('maintenance.show');
        Route::get('/{maintenanceRequest}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
        Route::put('/{maintenanceRequest}/details', [MaintenanceController::class, 'updateDetails'])->name('maintenance.details.update');
        Route::delete('/{maintenanceRequest}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
        Route::post('/{maintenanceRequest}/follow-up', [MaintenanceController::class, 'storeFollowUp'])->name('maintenance.follow-up');
        Route::patch('/{maintenanceRequest}/assign', [MaintenanceController::class, 'assign'])->name('maintenance.assign');
        Route::patch('/{maintenanceRequest}/status', [MaintenanceController::class, 'update'])->name('maintenance.update');
    });
    Route::get('/maintenance', function () {
        if (auth()->user()->isMaintenance()) {
            return redirect()->route('maint.dashboard');
        }

        return redirect()->route('maintenance.index');
    });

    Route::get('/landlord/communication', [LandlordCommunicationController::class, 'index'])->name('landlord.communication.index');
    Route::get('/landlord/communication/{emailTemplate}/edit', [LandlordCommunicationController::class, 'edit'])->name('landlord.communication.edit');
    Route::put('/landlord/communication/{emailTemplate}', [LandlordCommunicationController::class, 'update'])->name('landlord.communication.update');
    Route::delete('/landlord/communication/{emailTemplate}/reset', [LandlordCommunicationController::class, 'reset'])->name('landlord.communication.reset');

    Route::get('/documents/{document}/file', [DocumentFileController::class, 'show'])->name('documents.file');
    // Applications & Background Checks
    Route::get('/applications', [ApplicationController::class, 'landlordIndex'])->name('applications.index');
    Route::get('/background-checks', [ApplicationController::class, 'backgroundChecksIndex'])->name('background-checks.index');
    Route::post('/properties/{property}/applications',          [ApplicationController::class, 'store'])->name('applications.store');
    Route::patch('/applications/{application}/status',          [ApplicationController::class, 'updateStatus'])->name('applications.status');
    Route::post('/applications/{application}/background-checks',[ApplicationController::class, 'requestCheck'])->name('background-checks.store');
    Route::patch('/background-checks/{check}',                  [ApplicationController::class, 'updateCheck'])->name('background-checks.update');

});
