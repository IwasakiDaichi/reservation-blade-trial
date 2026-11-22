<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AdminReservationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarEntryController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\AdminVenueController;
use App\Http\Controllers\NotificationController;

// Public landing page
Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', PreventBackHistory::class])
    ->name('dashboard');

// Authenticated user routes
Route::middleware([
    'auth',
    PreventBackHistory::class
])->group(function () {

    // Profile
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

    // Reservation PDF printing
    Route::get('/reservations/{reservation}/print', [
        ReservationController::class,
        'print'
    ])->name('reservations.print');

    // Notices (students: messages from CPDO; CPDO: new requests and cancellations)
    Route::get('/notifications', [
        NotificationController::class,
        'index'
    ])->name('notifications.index');

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

    // Student cancels their own reservation, and asks to talk to a double-booking holder
    Route::patch('/reservations/{reservation}/cancel', [
        ReservationController::class,
        'cancel'
    ])->name('reservations.cancel');

    Route::post('/reservations/contact', [
        ReservationController::class,
        'contact'
    ])->name('reservations.contact');

    // Browse venues and see their details
    Route::get('/venues', [
        VenueController::class,
        'index'
    ])->name('venues.index');

    Route::get('/venues/{venue}', [
        VenueController::class,
        'show'
    ])->name('venues.show');

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

        // Reservation management
        Route::get('/reservations', [
            AdminReservationController::class,
            'index'
        ])->name('reservations.index');

        // Manage reservation signatures
        Route::get('/reservations/{reservation}/signatures', [
            AdminReservationController::class,
            'editSignatures'
        ])->name('reservations.signatures.edit');

        Route::put('/reservations/{reservation}/signatures', [
            AdminReservationController::class,
            'updateSignatures'
        ])->name('reservations.signatures.update');

        // CPDO cancels a reservation (with a reason the student can read)
        Route::patch('/reservations/{reservation}/cancel', [
            AdminReservationController::class,
            'cancel'
        ])->name('reservations.cancel');

        // Approve or reject reservations
        Route::patch('/reservations/{reservation}/{status}', [
            AdminReservationController::class,
            'updateStatus'
        ])
            ->whereIn('status', ['approved', 'rejected'])
            ->name('reservations.updateStatus');

        // Admin dashboard
        Route::get('/dashboard', [
            AdminDashboardController::class,
            'index'
        ])->name('dashboard');
        // Calendar of Activities (admin only)
        Route::get('/calendar', [
            CalendarController::class,
            'index'
        ])->name('calendar');

        Route::post('/calendar/entries', [
            CalendarEntryController::class,
            'store'
        ])->name('calendar.entries.store');

        Route::patch('/calendar/entries/{entry}', [
            CalendarEntryController::class,
            'update'
        ])->name('calendar.entries.update');

        Route::delete('/calendar/entries/{entry}', [
            CalendarEntryController::class,
            'destroy'
        ])->name('calendar.entries.destroy');

        Route::patch('/calendar/reservations/{reservation}/remarks', [
            CalendarEntryController::class,
            'remarks'
        ])->name('calendar.remarks');

        // Venue management (details and Active / Maintenance / Deactivated)
        Route::resource('venues', AdminVenueController::class)
            ->except(['show', 'destroy']);

        Route::patch('/venues/{venue}/status', [
            AdminVenueController::class,
            'status'
        ])->name('venues.status');

    });

// Google Authentication
Route::get('/auth/google', [
    GoogleAuthController::class,
    'redirect'
])->name('google.redirect');

Route::get('/auth/google/callback', [
    GoogleAuthController::class,
    'callback'
])->name('google.callback');

// Authentication routes
require __DIR__.'/auth.php';
