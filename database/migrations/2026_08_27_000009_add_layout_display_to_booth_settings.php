<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->string('layout_display_mode')->default('slideshow');
            $table->string('layout_display_size')->default('medium');
            $table->json('layout_visible_ids')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->dropColumn(['layout_display_mode', 'layout_display_size', 'layout_visible_ids']);
        });
    }
};