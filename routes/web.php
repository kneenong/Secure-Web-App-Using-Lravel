// routes/web.php
<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

// ... existing routes ...

// User Routes
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
    Route::patch('/profile', [UserDashboardController::class, 'updateProfile']);
    Route::post('/profile/upload-image', [UserDashboardController::class, 'uploadProfileImage'])->name('profile.upload');
    Route::get('/security', [UserDashboardController::class, 'security'])->name('security');
    Route::patch('/password', [UserDashboardController::class, 'updatePassword'])->name('password.update');
    Route::get('/activity', [UserDashboardController::class, 'activityLog'])->name('activity');
    Route::get('/activity/export', [UserDashboardController::class, 'exportActivity'])->name('activity.export');
    Route::get('/settings', [UserDashboardController::class, 'settings'])->name('settings');
    Route::patch('/settings', [UserDashboardController::class, 'updateSettings']);
    Route::get('/download-data', [UserDashboardController::class, 'downloadPersonalData'])->name('data.download');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'userManagement'])->name('users');
    Route::get('/users/{user}', [AdminDashboardController::class, 'userDetails'])->name('users.details');
    Route::post('/users/bulk-actions', [AdminDashboardController::class, 'bulkUserActions'])->name('users.bulk');
    Route::get('/system/logs', [AdminDashboardController::class, 'systemLogs'])->name('logs');
    Route::get('/system/logs/export', [AdminDashboardController::class, 'exportLogs'])->name('logs.export');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::patch('/settings', [AdminDashboardController::class, 'updateSettings']);
});