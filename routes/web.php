<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DashboardController;

Route::get('/', [UserController::class, 'home'])->name('home');

Route::middleware('auth')->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        # Schedules
        Route::get('/calendar', [ScheduleController::class, 'index'])->name('calendar');
        Route::post('/calendar', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::delete('/calendar', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

        # Appointments
        Route::get('/appointment', [AppointmentController::class, 'index'])->name('appointment');
        Route::post('/appointment', [AppointmentController::class, 'store'])->name('appointment.store');
        Route::patch('/appointment', [AppointmentController::class, 'update'])->name('appointment.update');
        Route::delete('/appointment', [AppointmentController::class, 'destroy'])->name('appointment.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
