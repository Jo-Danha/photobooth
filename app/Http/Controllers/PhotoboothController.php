<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\PhotoboothSession;
use App\Models\BoothSetting;
use App\Models\CustomFrame;
use App\Services\GoogleDriveService;
use Carbon\Carbon;

class PhotoboothController extends Controller
{
    public function index()
    {
        $setting = BoothSetting::getActiveSettings();

        $packages = [
            [
                'id' => 'strip_4',
                'name' => 'Classic Strip (4 Foto)',
                'description' => '4 Foto vertikal klasik gaya photobooth Korea/vintage',
                'shots' => 4,
                'duration' => 5,
                'price' => 15000,
                'popular' => true,
            ],
            [
                'id' => 'strip_3',
                'name' => 'Trio Strip (3 Foto)',
                'description' => '3 Frame foto vertikal proporsional dan estetik',
                'shots' => 3,
                'duration' => 5,
                'price' => 12000,
                'popular' => false,
            ],
            [
                'id' => 'strip_2',
                'name' => 'Duo Strip (2 Foto)',
                'description' => '2 Frame foto ekspresif ukuran besar',
                'shots' => 2,
                'duration' => 4,
                'price' => 10000,
                'popular' => false,
            ],
            [
                'id' => 'grid_4',
                'name' => 'Grid 2x2 (4 Foto)',
                'description' => 'Layout kotak 4 foto modern untuk Instagram & cetak',
                'shots' => 4,
                'duration' => 7,
                'price' => 20000,
                'popular' => false,
            ],
            [
                'id' => 'polaroid',
                'name' => 'Polaroid Retro',
                'description' => 'Format polaroid retro dengan ruang catatan di bawah',
                'shots' => 1,
                'duration' => 4,
                'price' => 8000,
                'popular' => false,
            ],
        ];

        return view('photobooth.index', compact('packages', 'setting'));
    }

    public function createSession(Request $request)
    {
        $request->validate([
            'layout_type' => 'required|string',
        ]);

        $setting = BoothSetting::getActiveSettings();

        $layoutMap = [
            'strip_4' => ['name' => 'Classic Strip 4-Shots', 'price' => 15000, 'duration' => 5],
            'strip_3' => ['name' => 'Trio Strip 3-Shots', 'price' => 12000, 'duration' => 5],
            'strip_2' => ['name' => 'Duo Strip 2-Shots', 'price' => 10000, 'duration' => 4],
            'grid_4'  => ['name' => 'Grid 2x2 Modern', 'price' => 20000, 'duration' => 7],
            'polaroid'=> ['name' => 'Polaroid Retro', 'price' => 8000, 'duration' => 4],
        ];

        $selected = $layoutMap[$request->layout_type] ?? $layoutMap['strip_4'];
        $sessionToken = Str::random(32);
        $orderId = 'PB-' . date('YmdHis') . '-' . strtoupper(Str::random(4));

        // Jika pembayaran dinonaktifkan di backend (Mode Event/Rental Gratis)
        if (!$setting->is_payment_enabled) {
            $now = Carbon::now();
            PhotoboothSession::create([
                'session_token'      => $sessionToken,
                'order_id'           => $orderId,
                'package_name'       => $selected['name'],
                'layout_type'        => $request->layout_type,
                'amount'             => 0,
                'payment_status'     => 'paid',
                'payment_method'     => 'FREE_EVENT_MODE',
                'payment_qr_url'     => null,
                'duration_minutes'   => $selected['duration'],
                'session_started_at' => $now,
                'session_expires_at' => $now->copy()->addMinutes($selected['duration']),
            ]);

            return redirect()->route('photobooth.studio', ['token' => $sessionToken]);
        }

        // Mode Pembayaran QRIS Aktif
        $qrisData = "00020101021226580014ID.LINKAJA.WWW01189360091100223456780215{$orderId}5204581253033605802ID5914PHOTOBOOTH-IO6007JAKARTA62070703A016304ABCD";
        $paymentQrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrisData);

        PhotoboothSession::create([
            'session_token'    => $sessionToken,
            'order_id'         => $orderId,
            'package_name'     => $selected['name'],
            'layout_type'      => $request->layout_type,
            'amount'           => $selected['price'],
            'payment_status'   => 'pending',
            'payment_method'   => 'QRIS',
            'payment_qr_url'   => $paymentQrUrl,
            'duration_minutes' => $selected['duration'],
        ]);

        return redirect()->route('photobooth.checkout', ['token' => $sessionToken]);
    }

    public function checkout($token)
    {
        $session = PhotoboothSession::where('session_token', $token)->firstOrFail();

        if ($session->payment_status === 'paid') {
            return redirect()->route('photobooth.studio', ['token' => $token]);
        }

        return view('photobooth.checkout', compact('session'));
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

        $remainingSeconds = $session->getRemainingSeconds();

        return view('photobooth.studio', compact('session', 'setting', 'customFrames', 'remainingSeconds'));
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

        return view('photobooth.download', compact('session'));
    }
}