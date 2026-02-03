<?php

use App\Http\Controllers\AdminFeedbackController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoadmapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PlayerController::class, 'index']);
Route::post('/api/validate', [PlayerController::class, 'validateUrl']);

// Guest-only auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::post('/feedback/{feedbackItem}/vote', [FeedbackController::class, 'vote'])->name('feedback.vote');
});

// Public feedback & roadmap
Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
Route::get('/feedback/{feedbackItem}', [FeedbackController::class, 'show'])->name('feedback.show');
Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::patch('/feedback/{feedbackItem}/status', [AdminFeedbackController::class, 'updateStatus'])->name('admin.feedback.status');
    Route::patch('/feedback/{feedbackItem}/respond', [AdminFeedbackController::class, 'respond'])->name('admin.feedback.respond');
    Route::delete('/feedback/{feedbackItem}', [AdminFeedbackController::class, 'destroy'])->name('admin.feedback.destroy');
});
