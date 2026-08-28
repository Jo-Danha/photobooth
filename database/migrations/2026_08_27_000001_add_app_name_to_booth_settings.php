<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booth_settings', 'app_name')) {
                $table->string('app_name')->default('PHOTOBOOTH.IO')->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booth_settings', 'app_name')) {
                $table->dropColumn('app_name');
            }
        });
    }
};
