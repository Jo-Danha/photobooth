<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->string('booth_mode')->default('mandiri')->after('is_lock_mode');
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->dropColumn('booth_mode');
        });
    }
};
