<?php

use App\Http\Controllers\LeadsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('/leads', LeadsController::class);
    Route::post('leads/create', [LeadsController::class, 'importExcel'])->name('leads.import');
    Route::post('leads/export', [LeadsController::class, 'exportExcel'])->name('leads.export');
});

require __DIR__.'/auth.php';
