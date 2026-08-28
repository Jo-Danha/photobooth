<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->text('google_oauth_refresh_token')->nullable()->after('google_drive_folder_id');
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->dropColumn('google_oauth_refresh_token');
        });
    }
};
