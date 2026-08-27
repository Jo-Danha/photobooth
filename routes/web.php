<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoboothController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Middleware\AdminAuthMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes - Photobooth System
|--------------------------------------------------------------------------
*/

// User Facing - Photobooth Workflow
Route::get('/', [PhotoboothController::class, 'index'])->name('photobooth.index');
Route::post('/session/create', [PhotoboothController::class, 'createSession'])->name('photobooth.session.create');

// Pembayaran & Polling
Route::get('/checkout/{token}', [PhotoboothController::class, 'checkout'])->name('photobooth.checkout');
Route::get('/checkout/{token}/status', [PhotoboothController::class, 'checkPaymentStatus'])->name('photobooth.checkout.status');
Route::post('/checkout/{token}/simulate-pay', [PhotoboothController::class, 'simulatePay'])->name('photobooth.simulate.pay');

// Studio Photobooth
Route::get('/studio/{token}', [PhotoboothController::class, 'studio'])->name('photobooth.studio');
Route::get('/studio/{token}/time-status', [PhotoboothController::class, 'checkRemainingTime'])->name('photobooth.studio.time');
Route::post('/studio/{token}/save', [PhotoboothController::class, 'saveResult'])->name('photobooth.studio.save');

// Hasil Akhir & Unduh
Route::get('/result/{token}', [PhotoboothController::class, 'result'])->name('photobooth.result');
Route::get('/download/{token}', [PhotoboothController::class, 'download'])->name('photobooth.download');

// Payment Webhook
Route::post('/webhook/payment/midtrans', [PaymentWebhookController::class, 'handleMidtransNotification'])->name('payment.webhook.midtrans');

// Admin Authentication (Login & Logout)
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Protected Admin Routes
Route::middleware([AdminAuthMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    Route::post('/frames/upload', [AdminController::class, 'uploadFrame'])->name('admin.frames.upload');
    Route::post('/frames/{id}/delete', [AdminController::class, 'deleteFrame'])->name('admin.frames.delete');
});