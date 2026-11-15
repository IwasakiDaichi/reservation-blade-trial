
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Middleware\PreventBackHistory;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', PreventBackHistory::class])
    ->name('dashboard');

// Profile routes
Route::middleware(['auth', PreventBackHistory::class])->group(function () {

    Route::patch('/profile/complete', [
        ProfileController::class,
        'completeStudentProfile'
    ])->name('profile.complete');

    Route::get('/profile', [
        ProfileController::class,
        'edit'
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update'
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy'
    ])->name('profile.destroy');

});

// Student routes
Route::middleware([
    'auth',
    'role:student',
    PreventBackHistory::class
])->group(function () {

    Route::get('/reservations', [
        ReservationController::class,
        'index'
    ])->name('reservations.index');

    Route::get('/reservations/create', [
        ReservationController::class,
        'create'
    ])->name('reservations.create');

    Route::post('/reservations', [
        ReservationController::class,
        'store'
    ])->name('reservations.store');

});

// CPDO Admin routes
Route::middleware([
    'auth',
    'role:cpdo_admin',
    PreventBackHistory::class
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/reservations', [
            AdminReservationController::class,
            'index'
        ])->name('reservations.index');

        Route::patch('/reservations/{reservation}/{status}', [
            AdminReservationController::class,
            'updateStatus',
        ])->whereIn('status', ['approved', 'rejected'])
          ->name('reservations.updateStatus');

        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index'
        ])->name('dashboard');

    });

// Google Authentication routes
Route::get('/auth/google', [
    GoogleAuthController::class,
    'redirect',
])->name('google.redirect');

Route::get('/auth/google/callback', [
    GoogleAuthController::class,
    'callback',
])->name('google.callback');

require __DIR__.'/auth.php';
