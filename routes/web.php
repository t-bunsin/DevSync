<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FeaturedJobController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\MyApplicationsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [JobController::class, 'landing'])->name('landing');

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])
    ->where('job', '[a-z0-9-]+')
    ->name('jobs.show');

// Applying is the one candidate action that needs an account, so the button
// always lands here first and this route decides where the visitor goes.
Route::get('/jobs/{job}/apply', [JobController::class, 'apply'])
    ->where('job', '[a-z0-9-]+')
    ->name('jobs.apply');

// The apply form itself. Auth is enforced here too, not just on the gate above,
// so the application can only ever be attributed to a real account.
Route::post('/jobs/{job}/apply', [JobController::class, 'storeApplication'])
    ->where('job', '[a-z0-9-]+')
    ->middleware('auth')
    ->name('jobs.apply.store');

Route::get('/language/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, ['en', 'kh'], true), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('language.switch');

Auth::routes();

// Every backend/admin-shell page lives under this prefix (route *names* are
// unchanged, so route() calls elsewhere didn't need touching). Auth is still
// enforced the same way as before — some controllers apply it themselves
// (BillingController, HomeController, ProfileController), the rest via the
// 'auth' group below — the prefix only changes the URL.
Route::prefix('admin')->group(function () {
    // The workspace dashboard, reached from Dashboards in the admin sidebar.
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::put('/profile', 'ProfileController@update')->name('profile.update');

    Route::get('/account-billing', [BillingController::class, 'index'])->name('account-billing');
    Route::get('/account-billing/checkout', [BillingController::class, 'checkout'])->name('account-billing.checkout');
    Route::post('/account-billing/pay', [BillingController::class, 'pay'])->name('account-billing.pay');
    Route::get('/account-billing/payway/return', [BillingController::class, 'paywayReturn'])->name('account-billing.payway.return');
    Route::get('/account-billing/payway/status', [BillingController::class, 'paywayStatus'])->name('account-billing.payway.status');
    // No 'auth' middleware: PayWay calls this server-to-server with no session. Also excluded from CSRF — see VerifyCsrfToken.
    Route::post('/account-billing/payway/callback', [BillingController::class, 'paywayCallback'])->name('account-billing.payway.callback');

    Route::middleware('auth')->group(function () {
        // The Activity center bell: polled by the admin shell for new arrivals.
        Route::get('/notifications', [NotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        // The candidate's own list — self-scoped by user_id in the controller,
        // so no role middleware is needed beyond being signed in.
        Route::get('/my-applications', [MyApplicationsController::class, 'index'])->name('my-applications');
        Route::get('/my-applications/{application}', [MyApplicationsController::class, 'show'])->name('my-applications.show');
        // Withdrawing deletes the row, so it is a DELETE — see JobApplication::isCancellable().
        Route::delete('/my-applications/{application}', [MyApplicationsController::class, 'destroy'])->name('my-applications.cancel');

        // Managing accounts is an administrator job. Everyone else is refused at
        // the door rather than served a filtered directory.
        Route::middleware('admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::resource('user', UserController::class);
        });

        // Admin-only: not part of any role's day-to-day menu.
        Route::middleware('admin')->group(function () {
            Route::post('/compliance/{compliance}/verify', [ComplianceController::class, 'verify'])
                ->name('compliance.verify');
            Route::resource('compliance', ComplianceController::class);

            // Creating and deleting companies stays an admin call. Editing does
            // not: an employer may edit their own record, so edit/update live in
            // the employer group below and CompaniesController checks ownership.
            Route::resource('companies', CompaniesController::class)
                ->only(['create', 'store', 'destroy']);

            // The Location/Department/Job type lists behind the job post form
            // (see JobPost::locationOptions()/departmentOptions()/types()).
            // {type} is one of locations|departments|job-types — a resource
            // route per type would just be the same six lines three times over.
            Route::get('/components/{type}', [ComponentController::class, 'index'])->name('components.index');
            Route::get('/components/{type}/create', [ComponentController::class, 'create'])->name('components.create');
            Route::post('/components/{type}', [ComponentController::class, 'store'])->name('components.store');
            Route::get('/components/{type}/{component}/edit', [ComponentController::class, 'edit'])->name('components.edit');
            Route::put('/components/{type}/{component}', [ComponentController::class, 'update'])->name('components.update');
            Route::delete('/components/{type}/{component}', [ComponentController::class, 'destroy'])->name('components.destroy');

            // Roles directory + the job post permission matrix, one page (RoleController).
            Route::get('/roles', [RoleController::class, 'index'])->name('roles');
            Route::patch('/roles/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

            // Curates the 'featured' flag on job posts — an admin call, not
            // something the employer-facing job post form exposes anymore.
            Route::get('/featured-jobs', [FeaturedJobController::class, 'index'])->name('featured-jobs');
            Route::patch('/featured-jobs/{jobPost}', [FeaturedJobController::class, 'toggle'])->name('featured-jobs.toggle');
        });

        // Employer manages their own job posts and the applications to them;
        // admin passes through every 'role:' gate regardless of the list.
        Route::middleware('role:employer')->group(function () {
            // Ahead of the resource route: /job-posts/{jobPost} would otherwise
            // swallow /job-posts/export and try to bind "export" as a post id.
            Route::get('/job-posts/export', [JobPostController::class, 'export'])->name('job-posts.export');
            Route::resource('job-posts', JobPostController::class)
                ->parameters(['job-posts' => 'jobPost']);

            // The candidates behind the Applications count on the job post list.
            Route::get('/job-posts/{jobPost}/applications', [JobApplicationController::class, 'index'])
                ->name('job-posts.applications');
            Route::get('/job-applications/{application}/cv', [JobApplicationController::class, 'downloadCv'])
                ->name('job-applications.cv');
            Route::get('/job-applications/{application}', [JobApplicationController::class, 'show'])
                ->name('job-applications.show');
            Route::patch('/job-applications/{application}', [JobApplicationController::class, 'update'])
                ->name('job-applications.update');
            Route::delete('/job-applications/{application}', [JobApplicationController::class, 'destroy'])
                ->name('job-applications.destroy');

            // Index keeps the bare `companies` name that the admin shell already links to.
            Route::get('/companies', [CompaniesController::class, 'index'])->name('companies');

            // Employer-or-admin at the route; CompaniesController::authorizeManage()
            // narrows it to the employer's own company, and keeps `status` an
            // admin-only field so nobody approves themselves.
            Route::get('/companies/{company}', [CompaniesController::class, 'show'])->name('companies.show');
            Route::get('/companies/{company}/edit', [CompaniesController::class, 'edit'])->name('companies.edit');
            Route::put('/companies/{company}', [CompaniesController::class, 'update'])->name('companies.update');

            // Resumes used to be admin-only at the route level; now the route
            // just requires employer-or-admin, and ResumeController checks the
            // resume.* permission for each action (see RoleController).
            Route::get('/resumes/{resume}/download', [ResumeController::class, 'download'])
                ->name('resumes.download');
            Route::resource('resumes', ResumeController::class);
        });
    });
});

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');


// use App\Http\Controllers\AuthController;

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
