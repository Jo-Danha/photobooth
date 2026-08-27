<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('photobooth_sessions', function (Blueprint $table) {
        $table->id();
        $table->string('session_token')->unique()->index();
        $table->string('order_id')->unique()->index();
        $table->string('package_name')->default('Classic Strip 4-Shots');
        $table->string('layout_type')->default('strip_4');
        $table->decimal('amount', 12, 2)->default(15000.00);
        $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed'])->default('pending');
        $table->string('payment_method')->nullable();
        $table->string('payment_qr_url')->nullable();
        $table->integer('duration_minutes')->default(5);
        $table->timestamp('session_started_at')->nullable();
        $table->timestamp('session_expires_at')->nullable();
        $table->string('result_image_path')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photobooth_sessions');
    }
};

