<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\RequestController as PublicRequestController;
use App\Http\Controllers\Dispatcher\RequestController as DispatcherController;
use App\Http\Controllers\Master\RequestController as MasterController;

// Публичная форма
Route::get('/', [PublicRequestController::class, 'create'])->name('requests.create');
Route::post('/requests', [PublicRequestController::class, 'store'])->name('requests.store');

Route::middleware(['auth'])->group(function () {
    // Диспетчер
    Route::middleware(['role:dispatcher'])->prefix('dispatcher')->name('dispatcher.')->group(function () {
        Route::get('/requests', [DispatcherController::class, 'index'])->name('requests.index');
        Route::get('/requests/{request}', [DispatcherController::class, 'show'])->name('requests.show');
        Route::patch('/requests/{request}/assign', [DispatcherController::class, 'assign'])->name('requests.assign');
        Route::patch('/requests/{request}/cancel', [DispatcherController::class, 'cancel'])->name('requests.cancel');
    });

    // Мастер
    Route::middleware(['role:master'])->prefix('master')->name('master.')->group(function () {
        Route::get('/requests', [MasterController::class, 'index'])->name('requests.index');
        Route::get('/requests/{request}', [MasterController::class, 'show'])->name('requests.show');
        Route::patch('/requests/{request}/take', [MasterController::class, 'take'])->name('requests.take');
        Route::patch('/requests/{request}/complete', [MasterController::class, 'complete'])->name('requests.complete');
    });
});

require __DIR__.'/auth.php';
