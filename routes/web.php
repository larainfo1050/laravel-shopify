<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/uploads/{id}', [DashboardController::class, 'show'])->name('dashboard.show');
Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');

// Upload
Route::get('/upload', [UploadController::class, 'create'])->name('upload.create');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
Route::delete('/uploads/{upload}', [UploadController::class, 'destroy'])->name('upload.destroy');
