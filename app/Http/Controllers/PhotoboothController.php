<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\PhotoboothSession;
use App\Models\BoothSetting;
use App\Models\CustomFrame;
use App\Services\GoogleDriveService;
use App\Services\QrisService;
use App\Mail\PhotoMail;
use Carbon\Carbon;

class PhotoboothController extends Controller
{
    public function index()
    {
        $setting = BoothSetting::getActiveSettings();

        $packages = collect(config('photobooth.packages'))->map(function ($pkg) use ($setting) {
            $pkg['price'] = $setting->getPriceForLayout($pkg['id']);
            return $pkg;
        });

        // Admin bisa memilih layout mana yg tampil (kosong = semua)
        $visibleIds = $setting->layout_visible_ids ?? [];
        if (!empty($visibleIds)) {
            $packages = $packages->filter(function ($pkg) use ($visibleIds) {
                return in_array($pkg['id'], $visibleIds);
            })->values();
        }

        $layoutDisplayMode = ($setting->layout_display_mode ?? 'slideshow') === 'auto'
            ? ($packages->count() <= 9 ? 'grid' : 'slideshow')
            : ($setting->layout_display_mode ?? 'slideshow');
        $layoutDisplaySize = $setting->layout_display_size ?? 'medium';

        $isLocked = (bool) ($setting->is_lock_mode ?? false);
        $autoScroll = (bool) ($setting->layout_auto_scroll ?? false);
        $autoScrollInterval = (int) ($setting->layout_auto_scroll_interval ?? 5);
        $checkedLayout = $packages->first()['id'] ?? null;

        return view('photobooth.index', compact(
            'packages',
            'setting',
            'layoutDisplayMode',
            'layoutDisplaySize',
            'isLocked',
            'autoScroll',
            'autoScrollInterval',
            'checkedLayout'
        ));
    }

    public function createSession(Request $request)
    {
        $request->validate([
            'layout_type' => 'required|string',
        ]);

        $setting = BoothSetting::getActiveSettings();

        $layoutMap = [];
        foreach (config('photobooth.packages') as $pkg) {
            $layoutMap[$pkg['id']] = [
                'name'     => $pkg['name'],
                'price'    => $setting->getPriceForLayout($pkg['id']),
                'duration' => $pkg['duration'],
            ];
        }

        $selected = $layoutMap[$request->layout_type] ?? $layoutMap['strip_4'];
        $sessionToken = Str::random(32);
        $orderId = 'PB-' . date('YmdHis') . '-' . strtoupper(Str::random(4));

        // Mode Manual (Wedding) — gratis persis photobooth-io.cc/index.html, tanpa QRIS/nominal
        if (($setting->booth_mode ?? 'mandiri') === 'manual') {
            $now = Carbon::now();
            $eventDuration = 60 * 24 * 7;
            PhotoboothSession::create([
                'session_token'      => $sessionToken,
                'order_id'           => $orderId,
                'package_name'       => $selected['name'],
                'layout_type'        => $request->layout_type,
                'amount'             => 0,
                'payment_status'     => 'paid',
                'payment_method'     => 'FREE_MANUAL_MODE',
                'payment_qr_url'     => null,
                'duration_minutes'   => $eventDuration,
                'session_started_at' => $now,
                'session_expires_at' => $now->copy()->addMinutes($eventDuration),
            ]);
            return redirect()->route('photobooth.studio', ['token' => $sessionToken]);
        }

        // Jika pembayaran dinonaktifkan di backend (Mode Event/Rental Gratis)
        if (!$setting->is_payment_enabled) {
            $now = Carbon::now();
            // Mode event: tanpa batas waktu (durasi sangat besar)
            $eventDuration = 60 * 24 * 7; // 7 hari
            PhotoboothSession::create([
                'session_token'      => $sessionToken,
                'order_id'           => $orderId,
                'package_name'       => $selected['name'],
                'layout_type'        => $request->layout_type,
                'amount'             => 0,
                'payment_status'     => 'paid',
                'payment_method'     => 'FREE_EVENT_MODE',
                'payment_qr_url'     => null,
                'duration_minutes'   => $eventDuration,
                'voucher_code'       => $request->filled('voucher_code') ? strtoupper($request->voucher_code) : null,
                'session_started_at' => $now,
                'session_expires_at' => $now->copy()->addMinutes($eventDuration),
            ]);

            return redirect()->route('photobooth.studio', ['token' => $sessionToken]);
        }

        // Tentukan metode pembayaran default (metode pertama yang diaktifkan admin)
        $enabledMethods = $setting->payment_methods ?: ['qris'];
        $defaultMethod = in_array('qris', array_map('strtolower', $enabledMethods)) ? 'QRIS' : strtoupper($enabledMethods[0]);

        $session = PhotoboothSession::create([
            'session_token'    => $sessionToken,
            'order_id'         => $orderId,
            'package_name'     => $selected['name'],
            'layout_type'      => $request->layout_type,
            'amount'           => $selected['price'],
            'payment_status'   => 'pending',
            'payment_method'   => $defaultMethod,
            'payment_qr_url'   => null,
            'duration_minutes' => $selected['duration'],
        ]);

        // Generate QRIS otomatis bila metode default adalah QRIS
        if ($defaultMethod === 'QRIS') {
            $qr = (new QrisService())->getQrImageUrl($session, $setting);
            $session->update(['payment_qr_url' => $qr]);
        }

        return redirect()->route('photobooth.checkout', ['token' => $sessionToken]);
    }

    public function checkout($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();

        if ($session->payment_status === 'paid') {
            return redirect()->route('photobooth.studio', ['token' => $token]);
        }

        $setting = BoothSetting::getActiveSettings();
        $qrUrl = (new QrisService())->getQrImageUrl($session, $setting);
        $methods = $setting->payment_methods ?: ['qris'];
        $bankAccount = $setting->bank_account;

        return view('photobooth.checkout', compact('session', 'qrUrl', 'methods', 'bankAccount'));
    }

    /**
     * Pengunjung memilih metode pembayaran di halaman checkout.
     * Memperbarui session agar laporan akurat, lalu regenerate QR bila QRIS.
     */
    public function selectPaymentMethod(Request $request, $token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();
        if ($session->payment_status === 'paid') {
            return response()->json(['success' => true, 'redirect' => route('photobooth.studio', ['token' => $token])]);
        }

        $method = strtoupper($request->input('method', 'QRIS'));
        $setting = BoothSetting::getActiveSettings();
        $enabled = array_map('strtoupper', $setting->payment_methods ?: ['qris']);
        if (!in_array($method, $enabled)) {
            $method = 'QRIS';
        }

        $session->payment_method = $method;
        if ($method === 'QRIS') {
            $session->payment_qr_url = (new QrisService())->getQrImageUrl($session, $setting);
        } else {
            $session->payment_qr_url = null;
        }
        $session->save();

        return response()->json([
            'success' => true,
            'qr_url'  => $session->payment_qr_url,
            'method'  => $method,
        ]);
    }

    /**
     * Webhook dari PSP (Midtrans/Xendit) untuk konfirmasi pembayaran otomatis.
     * Menandai session sebagai "paid" dan memulai sesi foto.
     */
    public function qrisWebhook(Request $request)
    {
        $orderId = $request->input('order_id')
            ?? $request->input('transaction_id')
            ?? ($request->input('transaction_details')['order_id'] ?? null);

        if (!$orderId) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $session = PhotoboothSession::where('order_id', $orderId)->first();
        if (!$session || $session->payment_status === 'paid') {
            return response()->json(['status' => 'ok'], 200);
        }

        // Deteksi status dari berbagai PSP
        $status = strtolower($request->input('transaction_status') ?? $request->input('status') ?? '');
        $paid = in_array($status, ['settlement', 'capture', 'paid', 'success', 'completed']);

        if ($paid) {
            $session->startSession();
            return response()->json(['status' => 'paid'], 200);
        }

        return response()->json(['status' => 'pending'], 200);
    }

    public function checkPaymentStatus($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();

        return response()->json([
            'status'     => $session->payment_status,
            'is_paid'    => $session->payment_status === 'paid',
            'studio_url' => route('photobooth.studio', ['token' => $token]),
        ]);
    }

    public function simulatePay(Request $request, $token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();

        if ($session->payment_status !== 'paid') {
            $session->startSession();
        }

        return redirect()->route('photobooth.studio', ['token' => $token]);
    }

    public function studio($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();
        $setting = BoothSetting::getActiveSettings();

        if ($session->payment_status !== 'paid') {
            return redirect()->route('photobooth.checkout', ['token' => $token]);
        }

        if (!$session->isSessionActive()) {
            return view('photobooth.expired', compact('session'));
        }

        // Ambil template frame grafis kustom yang cocok dengan layout
        $customFrames = CustomFrame::where('is_active', true)
            ->where('layout_type', $session->layout_type)
            ->get();

        // Tema dekoratif untuk layout bergambar (sesuai pilihan di halaman awal)
        $themedLayouts = ['hearts', 'dog', 'vintage', 'solace', 'classic', 'with_love', 'holidays', 'cat', 'bunny', 'fox', 'cool'];
        // Layout AR terpadu: frameTheme 'ar' → hanya AR picker, tanpa dekorasi frame statis.
        // 'ar' juga adalah AR-capable, jadi ditambahkan agar AR engine aktif di studio.
        $arLayouts = ['hearts', 'dog', 'cat', 'bunny', 'fox', 'cool', 'ar'];
        $frameTheme = in_array($session->layout_type, $themedLayouts) ? $session->layout_type : 'none';
        $isArLayout = in_array($session->layout_type, $arLayouts);

        // Ambil 1 frame PNG overlay yang cocok untuk di-composite ke canvas
        $customFrameUrl = null;
        $matchedFrame = $customFrames->first();
        if ($matchedFrame) {
            $customFrameUrl = asset($matchedFrame->frame_image_path);
        }

        $remainingSeconds = $session->getRemainingSeconds();

        // URL untuk QR code di dalam foto — arahkan ke GDrive jika sudah ada, atau /download/{token}
        $downloadUrl = !empty($session->metadata['gdrive_download'])
            ? $session->metadata['gdrive_download']
            : route('photobooth.download', ['token' => $session->session_token]);

        $hideChrome = true; // sembunyikan header/footer saat sesi foto (anti-salah-guna)
        return view('photobooth.studio', compact('session', 'setting', 'customFrames', 'frameTheme', 'isArLayout', 'customFrameUrl', 'remainingSeconds', 'downloadUrl', 'hideChrome'));
    }

    public function checkRemainingTime($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();

        return response()->json([
            'is_active'         => $session->isSessionActive(),
            'remaining_seconds' => $session->getRemainingSeconds(),
            'formatted_time'    => gmdate('i:s', $session->getRemainingSeconds()),
        ]);
    }

    public function saveResult(Request $request, $token, GoogleDriveService $driveService)
    {
        $request->validate([
            'image_data' => 'required|string',
        ]);

        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();

        $imageData = $request->image_data;
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $decoded = base64_decode($imageData);

        $filename = 'photobooth_' . $session->order_id . '_' . time() . '.png';

        $directory = public_path('photobooths');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($fullPath, $decoded);

        // Upload otomatis ke Google Drive jika service account ada
        $metadata = $session->metadata ?? [];
        $driveResult = $driveService->uploadFile($fullPath, $filename);
        if ($driveResult) {
            $metadata['gdrive_link'] = $driveResult['view_link'];
            $metadata['gdrive_download'] = $driveResult['download_link'];
        }

        $session->update([
            'result_image_path' => 'photobooths/' . $filename,
            'metadata' => $metadata,
            'expires_at' => Carbon::now()->addDays(3),
        ]);

        return response()->json([
            'success'   => true,
            'redirect'  => route('photobooth.result', ['token' => $token]),
            'image_url' => asset('photobooths/' . $filename),
        ]);
    }

    public function result($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();
        abort_if($session->isExpired(), 404, 'Sesi foto telah kedaluwarsa.');
        $setting = BoothSetting::getActiveSettings();

        if (!empty($session->metadata['gdrive_download'])) {
            $targetDownloadUrl = $session->metadata['gdrive_download'];
        } elseif (!empty($session->metadata['gdrive_link'])) {
            $targetDownloadUrl = $session->metadata['gdrive_link'];
        } elseif (!empty($setting->public_domain_url)) {
            $targetDownloadUrl = rtrim($setting->public_domain_url, '/') . '/download/' . $token;
        } else {
            $localIp = gethostbyname(gethostname());
            $port = request()->getPort() ? (':' . request()->getPort()) : ':8000';
            $targetDownloadUrl = "http://{$localIp}{$port}/download/{$token}";
        }

        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($targetDownloadUrl);

        return view('photobooth.result', compact('session', 'setting', 'targetDownloadUrl', 'qrCodeUrl'));
    }

    public function download($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();
        abort_if($session->isExpired(), 404, 'Sesi foto telah kedaluwarsa.');
        $setting = BoothSetting::getActiveSettings();

        return view('photobooth.download', compact('session', 'setting'));
    }

    // Serve file foto secara langsung (relative path -> bekerja di HP via domain publik)
    public function downloadFile($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();
        abort_if($session->isExpired(), 404, 'Sesi foto telah kedaluwarsa.');
        $path = public_path($session->result_image_path);
        abort_if(!file_exists($path), 404);

        return response()->file($path, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Kirim foto hasil ke email pengguna (Gmail/SMTP via Laravel Mail).
     */
    public function sendEmail(Request $request, $token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();
        $setting = BoothSetting::getActiveSettings();

        if (!$setting->enable_email) {
            return response()->json(['success' => false, 'message' => 'Fitur kirim email tidak diaktifkan.'], 403);
        }
        if ($session->isExpired()) {
            return response()->json(['success' => false, 'message' => 'Sesi foto telah kedaluwarsa.'], 410);
        }

        $request->validate(['email' => 'required|email|max:120']);

        $path = public_path($session->result_image_path);
        if (!file_exists($path)) {
            return response()->json(['success' => false, 'message' => 'File foto tidak ditemukan.'], 404);
        }

        try {
            if ($setting->email_from_name) {
                config(['mail.from.name' => $setting->email_from_name]);
            }
            Mail::to($request->input('email'))->send(new PhotoMail($path, $session->order_id, $setting->app_name));
            return response()->json(['success' => true, 'message' => 'Foto berhasil dikirim ke ' . $request->input('email')]);
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim email photobooth: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()]);
        }
    }

    public function gallery()
    {
        $setting = BoothSetting::getActiveSettings();

        // Mode Mandiri = privasi, galeri publik disembunyikan (404). Admin tetap lihat via /admin/gallery.
        if (($setting->booth_mode ?? 'mandiri') !== 'manual') {
            abort(404);
        }

        $sessions = PhotoboothSession::whereNotNull('result_image_path')
            ->orderBy('created_at', 'desc')
            ->limit(60)
            ->get();

        return view('photobooth.gallery', compact('setting', 'sessions'));
    }
}