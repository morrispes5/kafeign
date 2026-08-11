<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MenuItemController as AdminMenuItemController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TableEntryController;
use App\Http\Middleware\EnsureTableSession;
use Illuminate\Support\Facades\Route;

Route::get('/', [TableEntryController::class, 'showEntryForm'])->name('table.entry');
Route::post('/table', [TableEntryController::class, 'enter'])->name('table.enter');

Route::prefix('/table/{table:number}')
    ->middleware(EnsureTableSession::class)
    ->group(function () {
        Route::get('/', [TableEntryController::class, 'show'])->name('table.show');
        Route::get('/menu', [MenuController::class, 'index'])->name('table.menu');
        Route::get('/order', [OrderController::class, 'show'])->name('table.order');
        Route::get('/cart', [CartController::class, 'show'])->name('table.cart');

        // Throttled: these are the only endpoints an anonymous visitor can
        // use to change money-relevant data, so a scripted flood should hit
        // a wall long before it can run up somebody's bill.
        //
        // Note the split: everything under /cart touches only this
        // browser's session, while /cart/submit and the order-items delete
        // are the two that write to the shared tab.
        Route::middleware('throttle:20,1')->group(function () {
            Route::post('/cart/items', [CartController::class, 'store'])->name('table.cart.items.store');
            Route::patch('/cart/items', [CartController::class, 'update'])->name('table.cart.items.update');
            Route::delete('/cart/items/{menuItem}', [CartController::class, 'destroy'])->name('table.cart.items.destroy');
            Route::post('/cart/submit', [CartController::class, 'submit'])->name('table.cart.submit');

            Route::delete('/order-items/{orderItem}', [OrderController::class, 'destroy'])->name('table.order-items.destroy');
        });
    });

Route::prefix('admin')->name('admin.')->group(function () {
    // Bare /admin is what staff actually type (and bookmark). Send them
    // wherever they should be: straight to work if already signed in,
    // to the login form if not.
    Route::get('/', fn () => redirect()->route(
        auth()->check() ? 'admin.dashboard' : 'admin.login'
    ))->name('home');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [AdminOrderController::class, 'history'])->name('orders.history');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/receipt', [AdminOrderController::class, 'receipt'])->name('orders.receipt');
        Route::post('/orders/{order}/clear', [AdminOrderController::class, 'clear'])->name('orders.clear');
        Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');

        Route::patch('/menu-items/{menuItem}/toggle-availability', [AdminMenuItemController::class, 'toggleAvailability'])
            ->name('menu-items.toggle-availability');
        Route::patch('/menu-items/{menuItem}/stock', [AdminMenuItemController::class, 'updateStock'])
            ->name('menu-items.stock');
        Route::resource('menu-items', AdminMenuItemController::class)
            ->except('show')
            ->parameters(['menu-items' => 'menuItem']);
        Route::resource('categories', AdminCategoryController::class)->except('show');
    });
});
