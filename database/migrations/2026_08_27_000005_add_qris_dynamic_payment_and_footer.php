<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->string('qris_mode')->default('upload'); // upload | dynamic
            $table->text('qris_merchant_string')->nullable(); // Raw QRIS string dari bank/e-wallet (mode dynamic)
            $table->string('qris_provider')->nullable(); // midtrans | xendit | null
            $table->text('qris_api_key')->nullable(); // Server key / API key PSP
            $table->string('qris_merchant_id')->nullable(); // Merchant ID PSP (Xendit dll)
            $table->text('payment_methods')->nullable(); // JSON: ['qris','cash','transfer']
            $table->text('bank_account')->nullable(); // Info rekening/transfer untuk mode Tunai/Transfer
            $table->text('footer_text')->nullable(); // Teks footer halaman booth (editable admin)
        });
    }

    public function down(): void
    {
        Schema::table('booth_settings', function (Blueprint $table) {
            $table->dropColumn([
                'qris_mode',
                'qris_merchant_string',
                'qris_provider',
                'qris_api_key',
                'qris_merchant_id',
                'payment_methods',
                'bank_account',
                'footer_text',
            ]);
        });
    }
};
