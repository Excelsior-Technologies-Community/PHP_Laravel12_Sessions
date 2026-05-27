<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [SessionController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // revoke single device
    Route::delete('/sessions/revoke/{id}', [SessionController::class, 'revokeDevice'])
        ->name('sessions.revoke');

    // logout other devices
    Route::post('/sessions/logout-others', [SessionController::class, 'logoutOtherDevices'])
        ->name('sessions.logout-others');

    //  logout all route 
    Route::post('/sessions/logout-all', [SessionController::class, 'logoutAll'])
        ->name('sessions.logout-all');
});


require __DIR__.'/auth.php';