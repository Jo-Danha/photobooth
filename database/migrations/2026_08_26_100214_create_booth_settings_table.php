<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booth_settings', function (Blueprint $table) {
            $table->id();
            $table->string('camera_device_id')->nullable();
            $table->integer('camera_brightness')->default(100);
            $table->integer('camera_contrast')->default(100);
            $table->integer('camera_iso')->default(0);
            $table->integer('camera_saturation')->default(100);
            $table->string('default_brand_text')->default('PHOTOBOOTH.IO');
            $table->string('default_frame_color')->default('#FFFFFF');
            $table->boolean('is_lock_mode')->default(false);
            $table->boolean('lock_brand_text')->default(false);
            $table->boolean('lock_frame_color')->default(false);
            $table->string('admin_pin')->default('1234');
            $table->string('google_drive_folder_id')->nullable();
            $table->string('public_domain_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booth_settings');
    }
};