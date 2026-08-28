<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booth_settings', 'theme_color')) {
                $table->string('theme_color')->nullable()->default('#c2337d')->after('favicon_path');
            }
            if (!Schema::hasColumn('booth_settings', 'ui_language')) {
                $table->string('ui_language')->nullable()->default('id')->after('theme_color');
            }
            if (!Schema::hasColumn('booth_settings', 'business_logo_path')) {
                $table->string('business_logo_path')->nullable()->after('ui_language');
            }
            if (!Schema::hasColumn('booth_settings', 'logo_position')) {
                $table->string('logo_position')->nullable()->default('bottom-center')->after('business_logo_path');
            }
            if (!Schema::hasColumn('booth_settings', 'bg_music_path')) {
                $table->string('bg_music_path')->nullable()->after('logo_position');
            }
            if (!Schema::hasColumn('booth_settings', 'enable_countdown_sound')) {
                $table->boolean('enable_countdown_sound')->default(true)->after('bg_music_path');
            }
            if (!Schema::hasColumn('booth_settings', 'enable_greenscreen')) {
                $table->boolean('enable_greenscreen')->default(false)->after('enable_countdown_sound');
            }
            if (!Schema::hasColumn('booth_settings', 'greenscreen_bg_path')) {
                $table->string('greenscreen_bg_path')->nullable()->after('enable_greenscreen');
            }
            if (!Schema::hasColumn('booth_settings', 'event_voucher_code')) {
                $table->string('event_voucher_code')->nullable()->after('enable_greenscreen');
            }
            if (!Schema::hasColumn('booth_settings', 'default_photo_shape')) {
                $table->string('default_photo_shape')->nullable()->default('none')->after('event_voucher_code');
            }
            if (!Schema::hasColumn('booth_settings', 'lock_photo_shape')) {
                $table->boolean('lock_photo_shape')->default(false)->after('default_photo_shape');
            }
        });

        Schema::table('photobooth_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('photobooth_sessions', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('metadata');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $cols = [
                'theme_color', 'ui_language', 'business_logo_path', 'logo_position',
                'bg_music_path', 'enable_countdown_sound', 'enable_greenscreen',
                'greenscreen_bg_path', 'event_voucher_code', 'default_photo_shape', 'lock_photo_shape'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('booth_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('photobooth_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('photobooth_sessions', 'voucher_code')) {
                $table->dropColumn('voucher_code');
            }
        });
    }
};
