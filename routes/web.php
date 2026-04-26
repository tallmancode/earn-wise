<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CompanySwitchController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');

Route::inertia('/dashboard', 'Dashboard/Index')
    ->name('dashboard')
    ->middleware('auth');

Route::post('/company/switch', [CompanySwitchController::class, 'store'])
    ->name('company.switch')
    ->middleware('auth');
