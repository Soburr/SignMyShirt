<?php

use App\Http\Controllers\ShirtController;
use App\Http\Controllers\SignatureController;
use App\Models\Shirt;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::inertia('/shirts/create', 'CreateShirt')->name('shirts.create');

Route::post('/shirts', [ShirtController::class, 'store'])->name('shirts.store');

Route::get('/shirts/{shirt}', function (Shirt $shirt) {
    return Inertia::render('SignShirt', [
        'shirt' => $shirt->load('signatures'),
        'justCreated' => session('justCreated', false),
    ]);
})->name('shirts.show');

Route::post('/shirts/{shirt}/signatures', [SignatureController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('signatures.store');

require __DIR__.'/settings.php';