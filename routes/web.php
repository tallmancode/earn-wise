<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CommissionNoteController;
use App\Http\Controllers\CommissionNoteExportController;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

Route::post('/company/switch', [CompanySwitchController::class, 'store'])
    ->name('company.switch')
    ->middleware('auth');

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/companies/{company}/branches/{branch}/notes',
        [CommissionNoteController::class, 'index'])->name('notes.index');

    Route::post('/companies/{company}/branches/{branch}/notes',
        [CommissionNoteController::class, 'store'])
        ->name('notes.store')
        ->middleware('throttle:commission-writes');

    Route::patch('/notes/{note}',
        [CommissionNoteController::class, 'update'])
        ->name('notes.update')
        ->middleware('throttle:commission-writes');

    Route::delete('/notes/{note}',
        [CommissionNoteController::class, 'destroy'])
        ->name('notes.destroy')
        ->middleware('throttle:commission-writes');

    Route::get('/companies/{company}/branches/{branch}/notes/export',
        CommissionNoteExportController::class)->name('notes.export');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
});
