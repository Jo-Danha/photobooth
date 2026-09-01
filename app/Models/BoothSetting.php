<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoothSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'favicon_path',
        'camera_device_id',
        'camera_brightness',
        'camera_contrast',
        'camera_iso',
        'camera_saturation',
        'default_brand_text',
        'default_frame_color',
        'is_payment_enabled',
        'is_lock_mode',
        'lock_brand_text',
        'lock_frame_color',
        'admin_pin',
        'admin_username',
        'admin_password',
            'google_drive_folder_id',
            'google_oauth_refresh_token',
        'public_domain_url',
        'enable_email',
        'email_from_name',
        'qris_image_path',
        'layout_prices',
        'theme_color',
        'ui_language',
        'business_logo_path',
        'logo_position',
        'bg_music_path',
        'enable_countdown_sound',
        'enable_greenscreen',
        'greenscreen_bg_path',
        'event_voucher_code',
        'default_photo_shape',
        'lock_photo_shape',
        'qris_mode',
        'qris_merchant_string',
        'qris_provider',
        'qris_api_key',
        'qris_merchant_id',
        'payment_methods',
        'bank_account',
        'footer_text',
        'booth_mode',
        'layout_display_mode',
        'layout_display_size',
        'layout_visible_ids',
        'layout_auto_scroll',
        'layout_auto_scroll_interval',
    ];

    protected $casts = [
        'is_payment_enabled' => 'boolean',
        'enable_email' => 'boolean',
        'is_lock_mode' => 'boolean',
        'lock_brand_text' => 'boolean',
        'lock_frame_color' => 'boolean',
        'enable_countdown_sound' => 'boolean',
        'enable_greenscreen' => 'boolean',
        'lock_photo_shape' => 'boolean',
        'camera_brightness' => 'integer',
        'camera_contrast' => 'integer',
        'camera_iso' => 'integer',
        'camera_saturation' => 'integer',
        'layout_prices' => 'array',
        'payment_methods' => 'array',
        'layout_visible_ids' => 'array',
        'layout_auto_scroll' => 'boolean',
    ];

    /**
     * Ambil harga untuk layout tertentu (override dari admin jika ada).
     */
    public function getPriceForLayout(string $layoutType): int
    {
        $prices = $this->layout_prices ?? [];
        if (isset($prices[$layoutType]) && is_numeric($prices[$layoutType])) {
            return (int) $prices[$layoutType];
        }

        foreach (config('photobooth.packages', []) as $pkg) {
            if ($pkg['id'] === $layoutType) {
                return (int) $pkg['price'];
            }
        }

        return 0;
    }

    /**
     * Generate 11-shade brand palette dari theme_color agar seluruh UI
     * (tombol, header, aksen) mengikuti warna yang dipilih admin.
     */
    public function brandPalette(): array
    {
        $hex = $this->theme_color ?: '#c2337d';
        [$h, $s, $l] = $this->hexToHsl($hex);
        $stops = [50 => 0.97, 100 => 0.94, 200 => 0.86, 300 => 0.76, 400 => 0.65, 500 => 0.55, 600 => 0.48, 700 => 0.41, 800 => 0.35, 900 => 0.30, 950 => 0.20];
        $pal = [];
        foreach ($stops as $k => $light) {
            $pal[$k] = $this->hslToHex($h, $s, $light);
        }
        return $pal;
    }

    private function hexToHsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        $max = max($r, $g, $b); $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;
        $h = 0; $s = 0;
        if ($d !== 0) {
            $s = $d / (1 - abs(2 * $l - 1));
            switch ($max) {
                case $r: $h = 60 * fmod((($g - $b) / $d), 6); break;
                case $g: $h = 60 * ((($b - $r) / $d) + 2); break;
                case $b: $h = 60 * ((($r - $g) / $d) + 4); break;
            }
            if ($h < 0) $h += 360;
        }
        return [$h, $s, $l];
    }

    private function hslToHex(float $h, float $s, float $l): string
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        if ($h < 60) [$r, $g, $b] = [$c, $x, 0];
        elseif ($h < 120) [$r, $g, $b] = [$x, $c, 0];
        elseif ($h < 180) [$r, $g, $b] = [0, $c, $x];
        elseif ($h < 240) [$r, $g, $b] = [0, $x, $c];
        elseif ($h < 300) [$r, $g, $b] = [$x, 0, $c];
        else [$r, $g, $b] = [$c, 0, $x];
        $to = fn($v) => str_pad(dechex(round(($v + $m) * 255)), 2, '0', STR_PAD_LEFT);
        return '#' . $to($r) . $to($g) . $to($b);
    }

    public static function getActiveSettings()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'app_name' => 'PHOTOBOOTH.IO',
                'camera_brightness' => 100,
                'camera_contrast' => 100,
                'camera_iso' => 0,
                'camera_saturation' => 100,
                'default_brand_text' => 'PHOTOBOOTH.IO',
                'default_frame_color' => '#FFFFFF',
                'theme_color' => '#c2337d',
                'ui_language' => 'id',
                'logo_position' => 'bottom-center',
                'enable_countdown_sound' => true,
                'enable_greenscreen' => false,
                'default_photo_shape' => 'none',
                'lock_photo_shape' => false,
                'qris_mode' => 'upload',
                'payment_methods' => ['qris'],
                'footer_text' => 'Terima kasih telah menggunakan Photobooth kami. Selamat menikmati hasil fotonya!',
                'is_payment_enabled' => true,
                'enable_email' => false,
                'email_from_name' => null,
                'is_lock_mode' => false,
                'lock_brand_text' => false,
                'lock_frame_color' => false,
                'booth_mode' => 'mandiri',
                'admin_pin' => '1234',
                'admin_username' => 'admin',
                'admin_password' => 'admin123',
                'layout_display_mode' => 'slideshow',
                'layout_display_size' => 'medium',
                'layout_visible_ids' => [],
                'layout_auto_scroll' => false,
                'layout_auto_scroll_interval' => 5,
            ]
        );
    }
}