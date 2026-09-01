<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BoothSetting;
use App\Models\CustomFrame;
use App\Models\PhotoboothSession;
use App\Services\GoogleDriveService;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function loginForm()
    {
        if (session('is_admin_logged_in')) {
            return redirect()->route('admin.settings');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $setting = BoothSetting::getActiveSettings();

        // Login bisa pakai username/password ATAU PIN
        if (
            ($request->username === $setting->admin_username && $request->password === $setting->admin_password) ||
            ($request->password === $setting->admin_pin)
        ) {
            $request->session()->put('is_admin_logged_in', true);
            return redirect()->route('admin.settings')->with('success', 'Selamat datang di Panel Admin Photobooth!');
        }

        return back()->with('error', 'Username, Password, atau PIN salah.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('is_admin_logged_in');
        return redirect()->route('admin.login')->with('success', 'Anda telah berhasil logout.');
    }

    public function settings()
    {
        $setting = BoothSetting::getActiveSettings();
        $customFrames = CustomFrame::orderBy('id', 'desc')->get();
        $hasServiceAccount = file_exists(storage_path('app/service-account.json'));
        $hasOAuth = !empty($setting->google_oauth_refresh_token);
        $hasOAuthClient = !empty(env('GOOGLE_OAUTH_CLIENT_ID')) && !empty(env('GOOGLE_OAUTH_CLIENT_SECRET'));
        $localIp = gethostbyname(gethostname());
        $packages = config('photobooth.packages');
        $frameSizes = config('photobooth.frame_sizes');

        return view('admin.settings', compact('setting', 'customFrames', 'hasServiceAccount', 'hasOAuth', 'hasOAuthClient', 'localIp', 'packages', 'frameSizes'));
    }

    public function updateSettings(Request $request)
    {
        $setting = BoothSetting::getActiveSettings();

        $validated = $request->validate([
            'app_name'              => 'nullable|string|max:30',
            'camera_device_id'       => 'nullable|string',
            'camera_brightness'      => 'nullable|integer|min:20|max:200',
            'camera_contrast'       => 'nullable|integer|min:20|max:200',
            'camera_iso'            => 'nullable|integer|min:-100|max:100',
            'camera_saturation'     => 'nullable|integer|min:0|max:200',
            'default_brand_text'    => 'nullable|string|max:50',
            'default_frame_color'   => 'nullable|string|max:10',
            'admin_username'         => 'nullable|string|max:30',
            'admin_password'        => 'nullable|string|min:4|max:50',
            'admin_pin'             => 'nullable|string|min:4|max:8',
            'google_drive_folder_id' => 'nullable|string',
            'public_domain_url'     => 'nullable|url',
            'qris_image'            => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'favicon'              => 'nullable|image|mimes:ico,png,svg,jpg,jpeg|max:1024',
            'theme_color'          => 'nullable|string|max:9',
            'ui_language'          => 'nullable|string|in:id,en',
            'business_logo'        => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'logo_position'        => 'nullable|string|in:top-left,top-right,bottom-left,bottom-right,bottom-center,center',
            'bg_music'             => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'greenscreen_bg'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'event_voucher_code'   => 'nullable|string|max:30',
            'default_photo_shape'  => 'nullable|string|in:none,soft,circle,heart',
            'prices'               => 'nullable|array',
            'prices.*'             => 'nullable|numeric|min:0',
            'qris_mode'            => 'nullable|string|in:upload,dynamic',
            'qris_merchant_string' => 'nullable|string',
            'qris_provider'        => 'nullable|string|in:midtrans,xendit',
            'qris_api_key'         => 'nullable|string',
            'qris_merchant_id'     => 'nullable|string',
            'payment_methods'      => 'nullable|array',
            'payment_methods.*'    => 'nullable|string|in:qris,cash,transfer',
            'bank_account'         => 'nullable|string',
            'footer_text'          => 'nullable|string',
            'service_account_file' => 'nullable|file',
            'enable_email'         => 'nullable|boolean',
            'email_from_name'      => 'nullable|string|max:60',
            'booth_mode'           => 'nullable|string|in:mandiri,manual',
            'layout_display_mode'  => 'nullable|string|in:slideshow,grid,auto',
            'layout_display_size'  => 'nullable|string|in:small,medium,large',
            'layout_visible_ids'   => 'nullable|array',
            'layout_visible_ids.*' => 'nullable|string',
            'layout_auto_scroll'   => 'nullable|boolean',
            'layout_auto_scroll_interval' => 'nullable|integer|min:2|max:60',
        ]);

        // Merge-only: hanya perbarui field yang benar-benar dikirim form ini
        $data = [];
        foreach ($validated as $k => $v) {
            if ($request->has($k) || $request->hasFile($k)) {
                $data[$k] = $v;
            }
        }

        // Boolean flags (checkbox) — hanya diperbarui bila form membawanya
        foreach (['is_payment_enabled', 'is_lock_mode', 'lock_brand_text', 'lock_frame_color', 'enable_countdown_sound', 'enable_greenscreen', 'lock_photo_shape', 'enable_email'] as $b) {
            if ($request->has($b)) {
                $data[$b] = (bool) $request->input($b, 1);
            }
        }

        if ($request->has('prices')) {
            $data['layout_prices'] = $request->input('prices', []);
        }
        if ($request->has('payment_methods')) {
            $data['payment_methods'] = array_values(array_map('strtolower', $request->input('payment_methods', [])));
        }

        if ($request->hasFile('service_account_file')) {
            $request->file('service_account_file')->storeAs('', 'service-account.json', 'local');
        }

        // Upload QRIS resmi (hanya jika dikirim dari form pembayaran)
        if ($request->hasFile('qris_image')) {
            if ($setting->qris_image_path && file_exists(public_path($setting->qris_image_path))) {
                @unlink(public_path($setting->qris_image_path));
            }
            $file = $request->file('qris_image');
            $filename = 'qris_' . time() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('qris');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
            $validated['qris_image_path'] = 'qris/' . $filename;
        }

        if ($request->has('booth_mode')) {
            $data['booth_mode'] = $request->input('booth_mode');
        }

        // Tampilan booth (mode slideshow/grid, ukuran kartu, layout yg ditampilkan)
        if ($request->has('layout_display_mode')) {
            $data['layout_display_mode'] = $request->input('layout_display_mode');
        }
        if ($request->has('layout_display_size')) {
            $data['layout_display_size'] = $request->input('layout_display_size');
        }
        if ($request->has('layout_visible_ids')) {
            $selected = $request->input('layout_visible_ids', []);
            $allowed = array_column(config('photobooth.packages'), 'id');
            $data['layout_visible_ids'] = array_values(array_intersect($selected, $allowed));
        }
        if ($request->has('layout_auto_scroll')) {
            $data['layout_auto_scroll'] = (bool) $request->input('layout_auto_scroll', 0);
        }
        if ($request->has('layout_auto_scroll_interval')) {
            $data['layout_auto_scroll_interval'] = (int) $request->input('layout_auto_scroll_interval', 5);
        }

        // Harga per layout (hanya update bila form pembayaran mengirimkannya)
        if ($request->has('prices')) {
            $validated['layout_prices'] = $request->input('prices', []);
        }

        // Upload Favicon (hanya bila dikirim dari form brand/kiosk)
        if ($request->hasFile('favicon')) {
            if ($setting->favicon_path && file_exists(public_path($setting->favicon_path))) {
                @unlink(public_path($setting->favicon_path));
            }
            $file = $request->file('favicon');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('favicons');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
            $validated['favicon_path'] = 'favicons/' . $filename;
        }

        // Upload Logo Watermark Bisnis
        if ($request->hasFile('business_logo')) {
            if ($setting->business_logo_path && file_exists(public_path($setting->business_logo_path))) {
                @unlink(public_path($setting->business_logo_path));
            }
            $file = $request->file('business_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('logos');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
            $validated['business_logo_path'] = 'logos/' . $filename;
        }

        // Upload Musik Latar Booth
        if ($request->hasFile('bg_music')) {
            if ($setting->bg_music_path && file_exists(public_path($setting->bg_music_path))) {
                @unlink(public_path($setting->bg_music_path));
            }
            $file = $request->file('bg_music');
            $filename = 'bgmusic_' . time() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('audio');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
            $validated['bg_music_path'] = 'audio/' . $filename;
        }

        // Upload Background Virtual (Green Screen)
        if ($request->hasFile('greenscreen_bg')) {
            if ($setting->greenscreen_bg_path && file_exists(public_path($setting->greenscreen_bg_path))) {
                @unlink(public_path($setting->greenscreen_bg_path));
            }
            $file = $request->file('greenscreen_bg');
            $filename = 'gsbg_' . time() . '.' . $file->getClientOriginalExtension();
            $dir = public_path('backgrounds');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $filename);
            $validated['greenscreen_bg_path'] = 'backgrounds/' . $filename;
        }

        $setting->update($data);

        return redirect()->route('admin.settings')->with('success', 'Pengaturan berhasil diperbarui!');
    }

    public function uploadFrame(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'category'     => 'required|string|max:50',
            'layout_type'  => 'required|string',
            'frame_image'  => 'required|image|mimes:png,webp|max:5120',
        ]);

        $file = $request->file('frame_image');
        $filename = 'frame_' . time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();

        $destDir = public_path('frames');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0777, true);
        }

        $file->move($destDir, $filename);

        CustomFrame::create([
            'name'             => $request->name,
            'category'         => $request->category,
            'layout_type'      => $request->layout_type,
            'frame_image_path' => 'frames/' . $filename,
            'is_active'        => true,
        ]);

        return redirect()->route('admin.settings')->with('success', 'Template frame "' . $request->name . '" berhasil ditambahkan!');
    }

    public function deleteFrame($id)
    {
        $frame = CustomFrame::findOrFail($id);
        $fullPath = public_path($frame->frame_image_path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
        $frame->delete();

        return redirect()->route('admin.settings')->with('success', 'Template frame berhasil dihapus.');
    }

    /**
     * Generate & download contoh template SVG untuk sebuah layout.
     * SVG transparan + panduan kotak foto, bisa diedit di Illustrator/Figma/Inkscape.
     */
    public function downloadFrameTemplate($layout)
    {
        $sizes = config('photobooth.frame_sizes.' . $layout);
        if (!$sizes) {
            abort(404);
        }

        $w = $sizes['w'];
        $h = $sizes['h'];

        $shots = 0;
        foreach (config('photobooth.packages') as $pkg) {
            if ($pkg['id'] === $layout) { $shots = $pkg['shots']; break; }
        }

        $margin = 30;
        $spacing = 18;
        $footer = 90;

        if ($layout === 'polaroid') {
            $cols = 1; $rows = 1;
            $photoW = 360; $photoH = 360;
        } elseif (in_array($layout, ['strip_e', 'grid_4'])) {
            $cols = 2; $rows = 2;
            $photoW = 300; $photoH = 225;
        } elseif ($layout === 'strip_6') {
            $cols = 2; $rows = 3;
            $photoW = 300; $photoH = 225;
        } else {
            $cols = 1; $rows = $shots;
            $photoW = 340; $photoH = 240;
        }

        $slots = '';
        $n = 0;
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                if ($n >= ($layout === 'polaroid' ? 1 : $cols * $rows)) break;
                if ($layout === 'polaroid') {
                    $x = $margin; $y = $margin;
                } else {
                    $x = $margin + $c * ($photoW + $spacing);
                    $y = $margin + $r * ($photoH + $spacing);
                }
                $slots .= '<rect x="'.$x.'" y="'.$y.'" width="'.$photoW.'" height="'.$photoH.'" fill="#e2e8f0" fill-opacity="0.18" stroke="#94a3b8" stroke-width="2" stroke-dasharray="8 6"/>'
                        . '<text x="'.($x + 8).'" y="'.($y + 22).'" font-family="monospace" font-size="14" fill="#64748b">FOTO '.($n + 1).'</text>';
                $n++;
            }
        }

        $label = htmlspecialchars($layout, ENT_XML1);
        $svg = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'">'
            . '<rect x="6" y="6" width="'.($w - 12).'" height="'.($h - 12).'" fill="none" stroke="#d7529a" stroke-width="6"/>'
            . '<rect x="16" y="16" width="'.($w - 32).'" height="'.($h - 32).'" fill="none" stroke="#c2337d" stroke-width="2" stroke-dasharray="10 8"/>'
            . $slots
            . '<text x="20" y="'.($h - 56).'" font-family="sans-serif" font-size="20" font-weight="bold" fill="#c2337d">PHOTOBOOTH TEMPLATE - '.$label.'</text>'
            . '<text x="20" y="'.($h - 34).'" font-family="monospace" font-size="15" fill="#475569">Canvas '.$w.' x '.$h.' px | Template disarankan '.($w * 3).' x '.($h * 3).' px</text>'
            . '<text x="20" y="'.($h - 16).'" font-family="monospace" font-size="13" fill="#64748b">Area abu-abu = foto. Taruh dekorasi di tepi (transparan).</text>'
            . '</svg>';

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="template_'.$layout.'.svg"');
    }

    public function reports(Request $request)
    {
        $setting = BoothSetting::getActiveSettings();

        $date = $request->input('date', Carbon::today()->toDateString());
        $sessions = PhotoboothSession::where('payment_method', 'QRIS')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $sessions->sum('amount');
        $count = $sessions->count();

        $daily = PhotoboothSession::where('payment_method', 'QRIS')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::today()->subDays(13))
            ->selectRaw('DATE(created_at) as tgl, SUM(amount) as total, COUNT(*) as jml')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        return view('admin.reports', compact('setting', 'sessions', 'total', 'count', 'date', 'daily'));
    }

    public function exportReportCsv(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $sessions = PhotoboothSession::where('payment_method', 'QRIS')
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "Order ID,Tanggal,Waktu,Jenis Paket,Layout,Durasi (menit),Total (Rp),Voucher\n";
        foreach ($sessions as $s) {
            $csv .= '"' . $s->order_id . '",'
                . $s->created_at->format('Y-m-d') . ','
                . $s->created_at->format('H:i:s') . ','
                . '"' . str_replace('"', '""', $s->package_name) . '",'
                . $s->layout_type . ','
                . $s->duration_minutes . ','
                . $s->amount . ','
                . ($s->voucher_code ?: '') . "\n";
        }

        $filename = 'laporan_qris_' . $date . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function gallery()
    {
        $setting = BoothSetting::getActiveSettings();
        $sessions = PhotoboothSession::whereNotNull('result_image_path')
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('admin.gallery', compact('setting', 'sessions'));
    }

    /**
     * Mulai alur OAuth: redirect ke Google consent.
     */
    public function gdriveConnect(GoogleDriveService $driveService)
    {
        $url = $driveService->connectUrl();
        if (!$url) {
            return redirect()->back()->with('error', 'Isi GOOGLE_OAUTH_CLIENT_ID & GOOGLE_OAUTH_CLIENT_SECRET di .env dulu.');
        }
        return redirect()->away($url);
    }

    /**
     * Callback dari Google setelah login + consent.
     */
    public function gdriveCallback(Request $request)
    {
        $driveService = new GoogleDriveService();
        if ($request->has('error')) {
            return redirect()->route('admin.settings')
                ->with('error', 'OAuth dibatalkan: ' . $request->get('error'));
        }
        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('admin.settings')->with('error', 'Kode OAuth tidak valid.');
        }

        $result = $driveService->handleCallback($code);

        if ($result['ok']) {
            return redirect()->route('admin.settings')->with('success', 'Terhubung ke Google Drive: ' . $result['message']);
        }
        return redirect()->route('admin.settings')->with('error', $result['message']);
    }

    /**
     * Putuskan koneksi OAuth.
     */
    public function gdriveDisconnect(GoogleDriveService $driveService)
    {
        $driveService->disconnect();
        return redirect()->route('admin.settings')->with('success', 'Koneksi Google Drive (OAuth) dicabut.');
    }

    /**
     * Uji koneksi Google Drive (dipanggil via AJAX dari menu pengaturan).
     */
    public function testGoogleDrive(GoogleDriveService $driveService)
    {
        $result = $driveService->testConnection();
        return response()->json($result);
    }

    /**
     * Upload preview custom per layout (admin).
     */
    public function uploadLayoutPreview(Request $request)
    {
        $request->validate([
            'layout_type' => 'required|string',
            'preview_image' => 'required|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $allowed = array_column(config('photobooth.packages'), 'id');
        if (!in_array($request->layout_type, $allowed)) {
            return redirect()->back()->with('error', 'Layout tidak valid.');
        }

        $dir = public_path('layout-previews');
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $ext = strtolower($request->file('preview_image')->getClientOriginalExtension());
        if (!in_array($ext, ['png','jpg','jpeg','webp'])) $ext = 'png';

        // Selalu simpan sebagai .png agar index.blade pakai path yang sama (konversi)
        $dest = $dir . '/' . $request->layout_type . '.png';
        $tmp = $request->file('preview_image')->getPathname();

        // Konversi ke PNG via GD agar konsisten
        $src = null;
        if ($ext === 'png') $src = @imagecreatefrompng($tmp);
        elseif (in_array($ext, ['jpg','jpeg'])) $src = @imagecreatefromjpeg($tmp);
        elseif ($ext === 'webp') $src = @imagecreatefromwebp($tmp);

        if ($src) {
            imagepng($src, $dest);
            imagedestroy($src);
        } else {
            // fallback copy as-is
            move_uploaded_file($tmp, $dest);
        }

        return redirect()->back()->with('success', 'Preview ' . $request->layout_type . ' berhasil diupload.');
    }
}