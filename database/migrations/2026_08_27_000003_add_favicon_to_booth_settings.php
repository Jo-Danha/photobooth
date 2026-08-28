<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('booth_settings', 'favicon_path')) {
                $table->string('favicon_path')->nullable()->after('app_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            if (Schema::hasColumn('booth_settings', 'favicon_path')) {
                $table->dropColumn('favicon_path');
            }
        });
    }
};
