<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoothSetting extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'public_domain_url',
    ];

    protected $casts = [
        'is_payment_enabled' => 'boolean',
        'is_lock_mode' => 'boolean',
        'lock_brand_text' => 'boolean',
        'lock_frame_color' => 'boolean',
        'camera_brightness' => 'integer',
        'camera_contrast' => 'integer',
        'camera_iso' => 'integer',
        'camera_saturation' => 'integer',
    ];

    public static function getActiveSettings()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'camera_brightness' => 100,
                'camera_contrast' => 100,
                'camera_iso' => 0,
                'camera_saturation' => 100,
                'default_brand_text' => 'PHOTOBOOTH.IO',
                'default_frame_color' => '#FFFFFF',
                'is_payment_enabled' => true,
                'is_lock_mode' => false,
                'lock_brand_text' => false,
                'lock_frame_color' => false,
                'admin_pin' => '1234',
                'admin_username' => 'admin',
                'admin_password' => 'admin123',
            ]
        );
    }
}