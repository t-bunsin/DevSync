<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompaniesController;
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

Route::get('/', function () {
    return view('main');
});

Route::get('/language/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, ['en', 'kh'], true), 404);

    session(['locale' => $locale]);

    return redirect()->back();
})->name('language.switch');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/profile', 'ProfileController@index')->name('profile');
Route::put('/profile', 'ProfileController@update')->name('profile.update');



Route::get('/users', [UserController::class, 'index'])->name('users');
Route::get('/companies', [CompaniesController::class, 'index'])->name('companies');

Route::resource('user', UserController::class);

Route::get('/about', function () {
    return view('about');
})->name('about');


// use App\Http\Controllers\AuthController;

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::post('/users/register', [UserController::class, 'register'])->name('users.register');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
