<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TableEntryController::class, 'showEntryForm'])->name('table.entry');
Route::post('/table', [TableEntryController::class, 'enter'])->name('table.enter');

Route::prefix('/table/{table:number}')->group(function () {
    Route::get('/', [TableEntryController::class, 'show'])->name('table.show');
    Route::get('/menu', [MenuController::class, 'index'])->name('table.menu');
    Route::get('/order', [OrderController::class, 'show'])->name('table.order');
    Route::post('/order-items', [OrderController::class, 'store'])->name('table.order-items.store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/clear', [AdminOrderController::class, 'clear'])->name('orders.clear');
        Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
    });
});
