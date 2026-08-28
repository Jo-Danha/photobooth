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
Route::post('/checkout/{token}/method', [PhotoboothController::class, 'selectPaymentMethod'])->name('photobooth.checkout.method');

// Payment Webhook (generic PSP / Xendit)
Route::post('/webhook/payment/qris', [PhotoboothController::class, 'qrisWebhook'])->name('payment.webhook.qris');

// Studio Photobooth
Route::get('/studio/{token}', [PhotoboothController::class, 'studio'])->name('photobooth.studio');
Route::get('/studio/{token}/time-status', [PhotoboothController::class, 'checkRemainingTime'])->name('photobooth.studio.time');
Route::post('/studio/{token}/save', [PhotoboothController::class, 'saveResult'])->name('photobooth.studio.save');

// Hasil Akhir & Unduh
Route::get('/result/{token}', [PhotoboothController::class, 'result'])->name('photobooth.result');
Route::get('/download/{token}', [PhotoboothController::class, 'download'])->name('photobooth.download');
Route::get('/file/{token}', [PhotoboothController::class, 'downloadFile'])->name('photobooth.file');
Route::post('/email/{token}', [PhotoboothController::class, 'sendEmail'])->name('photobooth.email');
Route::get('/gallery', [PhotoboothController::class, 'gallery'])->name('photobooth.gallery');

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
    Route::get('/frames/template/{layout}/download', [AdminController::class, 'downloadFrameTemplate'])->name('admin.frames.template.download');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/reports/export', [AdminController::class, 'exportReportCsv'])->name('admin.reports.export');
    Route::get('/gallery', [AdminController::class, 'gallery'])->name('admin.gallery');
    Route::post('/gdrive/test', [AdminController::class, 'testGoogleDrive'])->name('admin.gdrive.test');
    Route::get('/gdrive/connect', [AdminController::class, 'gdriveConnect'])->name('admin.gdrive.connect');
    Route::get('/gdrive/callback', [AdminController::class, 'gdriveCallback'])->name('admin.gdrive.callback');
    Route::post('/gdrive/disconnect', [AdminController::class, 'gdriveDisconnect'])->name('admin.gdrive.disconnect');
    Route::post('/layout-preview/upload', [AdminController::class, 'uploadLayoutPreview'])->name('admin.layout.preview.upload');
});