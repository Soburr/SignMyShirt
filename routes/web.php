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

// GET: the design page where a user builds a new shirt
Route::inertia('/shirts/create', 'CreateShirt')->name('shirts.create');

// POST: receives that design and creates the shirt record
Route::post('/shirts', [ShirtController::class, 'store'])->name('shirts.store');

// GET: the shared link — renders the shirt for viewing/signing
Route::get('/shirts/{shirt}', function (Shirt $shirt) {
    return Inertia::render('SignShirt', [
        'shirt' => $shirt->load('signatures'),
    ]);
})->name('shirts.show');

// POST: add one signature to a shirt, rate-limited to curb spam
Route::post('/shirts/{shirt}/signatures', [SignatureController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('signatures.store');

require __DIR__.'/settings.php';