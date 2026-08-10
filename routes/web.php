<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\TableEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TableEntryController::class, 'showEntryForm'])->name('table.entry');
Route::post('/table', [TableEntryController::class, 'enter'])->name('table.enter');

Route::prefix('/table/{table:number}')->group(function () {
    Route::get('/', [TableEntryController::class, 'show'])->name('table.show');
    Route::get('/menu', [MenuController::class, 'index'])->name('table.menu');
});
