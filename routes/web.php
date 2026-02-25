<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrendingController;
use App\Http\Controllers\WatchController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\WatchProgressController;
use App\Http\Controllers\ViewingHistoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BrowseController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/locale/{locale}', [HomeController::class, 'switchLocale'])->name('locale.switch');

// Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Legal (public)
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/dmca', [PageController::class, 'dmca'])->name('dmca');

// Profile selection (auth required, no profile needed)
Route::middleware('auth')->group(function () {
    Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
    Route::post('/profiles', [ProfileController::class, 'store'])->name('profiles.store');
    Route::post('/profiles/deselect', [ProfileController::class, 'deselect'])->name('profiles.deselect');
    Route::post('/profiles/{profile}/select', [ProfileController::class, 'select'])->name('profiles.select');
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy'])->name('profiles.destroy');

    Route::get('/settings', fn() => redirect()->route('settings.profile'));
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::get('/settings/account', [SettingsController::class, 'account'])->name('settings.account');
    Route::put('/settings/account', [SettingsController::class, 'updateAccount'])->name('settings.account.update');
    Route::get('/settings/password', [SettingsController::class, 'password'])->name('settings.password');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::get('/settings/deleteaccount', [SettingsController::class, 'deleteAccount'])->name('settings.deleteaccount');
    Route::delete('/settings/deleteaccount', [SettingsController::class, 'destroyAccount'])->name('settings.deleteaccount.destroy');
});

// Protected (auth + profile required)
Route::middleware(['auth', 'profile'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/movie/{id}', [DetailController::class, 'movie'])->name('movie.show')->where('id', '[0-9]+');
    Route::get('/tv/{id}', [DetailController::class, 'tv'])->name('tv.show')->where('id', '[0-9]+');
    Route::get('/api/tv/{id}/season/{season}', [DetailController::class, 'seasonEpisodes'])->name('api.tv.season')->where(['id' => '[0-9]+', 'season' => '[0-9]+']);
    Route::get('/person/{id}', [PersonController::class, 'show'])->name('person.show')->where('id', '[0-9]+');

    Route::get('/trending', [TrendingController::class, 'index'])->name('trending.index');
    Route::get('/api/trending', [TrendingController::class, 'loadMore'])->name('trending.loadMore');

    Route::get('/browse', [BrowseController::class, 'index'])->name('browse.index');
    Route::get('/api/browse', [BrowseController::class, 'loadMore'])->name('browse.loadMore');

    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');

    Route::get('/watch/movie/{id}', [WatchController::class, 'movie'])->name('watch.movie')->where('id', '[0-9]+');
    Route::get('/watch/tv/{id}-{season}-{episode}', [WatchController::class, 'tv'])->name('watch.tv')->where(['id' => '[0-9]+', 'season' => '[0-9]+', 'episode' => '[0-9]+']);
    Route::post('/api/watch-progress', [WatchProgressController::class, 'store'])->name('watch.progress.store');

    Route::get('/viewing-history', [ViewingHistoryController::class, 'index'])->name('viewing_history.index');
    Route::delete('/viewing-history', [ViewingHistoryController::class, 'destroyAll'])->name('viewing_history.destroy_all');
    Route::delete('/viewing-history/{id}', [ViewingHistoryController::class, 'destroy'])->name('viewing_history.destroy')->where('id', '[0-9]+');
});
