<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;

Route::get('/', [ContactController::class, 'index'])->name('contacts.index');

Route::prefix('contacts')->name('contacts.')->group(function () {
    Route::get('/list', [ContactController::class, 'getContacts'])->name('list');
    Route::post('/', [ContactController::class, 'store'])->name('store');
    Route::get('/{id}', [ContactController::class, 'show'])->name('show');
    Route::put('/{id}', [ContactController::class, 'update'])->name('update');
    Route::delete('/{id}', [ContactController::class, 'destroy'])->name('destroy');
    Route::get('/merge/list', [ContactController::class, 'getMergeContacts'])->name('merge.list');
    Route::post('/merge', [ContactController::class, 'merge'])->name('merge');
});

Route::prefix('custom-fields')->name('custom-fields.')->group(function () {
    Route::get('/', [CustomFieldController::class, 'index'])->name('index');
    Route::post('/', [CustomFieldController::class, 'store'])->name('store');
    Route::get('/{id}', [CustomFieldController::class, 'show'])->name('show');
    Route::put('/{id}', [CustomFieldController::class, 'update'])->name('update');
    Route::delete('/{id}', [CustomFieldController::class, 'destroy'])->name('destroy');
});