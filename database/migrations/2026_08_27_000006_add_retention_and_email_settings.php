<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photobooth_sessions', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('session_expires_at');
        });

        Schema::table('booth_settings', function (Blueprint $table) {
            $table->boolean('enable_email')->default(false)->after('public_domain_url');
            $table->string('email_from_name')->nullable()->after('enable_email');
        });
    }

    public function down(): void
    {
        Schema::table('photobooth_sessions', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        Schema::table('booth_settings', function (Blueprint $table) {
            $table->dropColumn(['enable_email', 'email_from_name']);
        });
    }
};
