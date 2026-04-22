<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('/login', 'Auth/Login')->name('login');

Route::inertia('/dashboard', 'Dashboard/Index')
    ->name('dashboard')
    ->middleware('auth');
