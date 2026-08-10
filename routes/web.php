<?php

use App\Http\Controllers\AdditionalFundController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/revenue/daily/ps', [DashboardController::class, 'dailyRevenuePs'])->name('revenue.daily.ps');
    Route::get('/revenue/daily/food-drinks', [DashboardController::class, 'dailyRevenueFoodDrinks'])->name('revenue.daily.food-drinks');
    Route::get('/revenue/archive/ps', [DashboardController::class, 'archiveRevenuePs'])->name('revenue.archive.ps');
    Route::get('/revenue/archive/ps/export', [DashboardController::class, 'exportArchiveRevenuePs'])->name('revenue.archive.ps.export');
    Route::get('/revenue/archive/food-drinks', [DashboardController::class, 'archiveRevenueFoodDrinks'])->name('revenue.archive.food-drinks');
    Route::get('/revenue/archive/food-drinks/export', [DashboardController::class, 'exportArchiveRevenueFoodDrinks'])->name('revenue.archive.food-drinks.export');

    Route::resource('units', UnitController::class)->except(['show']);
    Route::resource('packages', PackageController::class)->except(['show']);
    Route::resource('members', MemberController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('bookings', BookingController::class)->except(['show']);
    Route::patch('bookings/{booking}/update-status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::resource('expenses', ExpenseController::class)->except(['show']);
    Route::resource('additional-funds', AdditionalFundController::class)->except(['show']);
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'update', 'destroy']);
    Route::get('food-drinks', [TransactionController::class, 'foodDrinksPage'])->name('food-drinks');
    Route::post('food-drinks', [TransactionController::class, 'storeFoodDrinks'])->name('food-drinks.store');
    Route::post('transactions/{transaction}/finish', [TransactionController::class, 'finish'])->name('transactions.finish');
    Route::post('transactions/{transaction}/complete-payment', [TransactionController::class, 'completePayment'])->name('transactions.complete-payment');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
