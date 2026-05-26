<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

// 1. Home Page
Route::get('/', function () {
    return view('welcome');
});

// 2. Dashboard - Handled by SessionController for better data safety
Route::get('/dashboard', [SessionController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 3. Authenticated User Routes
Route::middleware('auth')->group(function () {
    
    // --- Profile Management ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Browser Session Security Actions ---
    
    // Revoke a specific session by ID (Single Logout)
    Route::delete('/sessions/revoke/{id}', [SessionController::class, 'revokeDevice'])
        ->name('sessions.revoke');

    // Terminate all other sessions (Bulk Logout)
    Route::post('/sessions/logout-others', [SessionController::class, 'logoutOtherDevices'])
        ->name('sessions.logout-others');

});

Route::post('/logout-all', [SessionController::class, 'logoutAll']);

require __DIR__.'/auth.php';