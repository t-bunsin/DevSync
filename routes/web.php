<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\ResumeController;
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

Route::get('/language/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, ['en', 'kh'], true), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('language.switch');

Auth::routes();

// The workspace dashboard, reached from Dashboards in the admin sidebar.
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/profile', 'ProfileController@index')->name('profile');
Route::put('/profile', 'ProfileController@update')->name('profile.update');




Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::resource('user', UserController::class);

    Route::post('/compliance/{compliance}/verify', [ComplianceController::class, 'verify'])
        ->name('compliance.verify');
    Route::resource('compliance', ComplianceController::class);

    Route::resource('job-posts', JobPostController::class)
        ->parameters(['job-posts' => 'jobPost']);

    Route::get('/resumes/{resume}/download', [ResumeController::class, 'download'])
        ->name('resumes.download');
    Route::resource('resumes', ResumeController::class);

    // Index keeps the bare `companies` name that the admin shell already links to.
    Route::get('/companies', [CompaniesController::class, 'index'])->name('companies');
    Route::resource('companies', CompaniesController::class)->except(['index']);
});

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');


// use App\Http\Controllers\AuthController;

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
