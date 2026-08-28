<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booth_settings', 'qris_image_path')) {
                $table->string('qris_image_path')->nullable()->after('public_domain_url');
            }
            if (!Schema::hasColumn('booth_settings', 'layout_prices')) {
                $table->json('layout_prices')->nullable()->after('qris_image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booth_settings', 'qris_image_path')) {
                $table->dropColumn('qris_image_path');
            }
            if (Schema::hasColumn('booth_settings', 'layout_prices')) {
                $table->dropColumn('layout_prices');
            }
        });
    }
};
