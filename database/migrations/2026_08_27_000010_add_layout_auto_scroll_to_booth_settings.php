<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->boolean('layout_auto_scroll')->default(false);
            $table->unsignedInteger('layout_auto_scroll_interval')->default(5);
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->dropColumn(['layout_auto_scroll', 'layout_auto_scroll_interval']);
        });
    }
};