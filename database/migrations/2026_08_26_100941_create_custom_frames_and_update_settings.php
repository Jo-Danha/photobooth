<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Frame Kustom Bergambar
        if (!Schema::hasTable('custom_frames')) {
            Schema::create('custom_frames', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->default('Umum');
                $table->string('layout_type')->default('strip_4');
                $table->string('frame_image_path');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Tambahan Kolom Pengaturan Pembayaran & Login
        Schema::table('booth_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booth_settings', 'is_payment_enabled')) {
                $table->boolean('is_payment_enabled')->default(true);
            }
            if (!Schema::hasColumn('booth_settings', 'admin_username')) {
                $table->string('admin_username')->default('admin');
            }
            if (!Schema::hasColumn('booth_settings', 'admin_password')) {
                $table->string('admin_password')->default('admin123');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_frames');
    }
};