<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

Route::get('/', [UserController::class, 'home'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
        Route::post('/calendar', [DashboardController::class, 'store'])->name('calendar.store');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Testing the holidayCrawler
// ---------------------------
// Route::get('/crawl-holidays', function (HolidayCrawler $crawler) {
//     // $holidays = $crawler->fetchHolidays();
//     // return response()->json($holidays);
// });

require __DIR__ . '/auth.php';
