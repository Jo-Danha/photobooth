<?php

namespace App\Services;

use App\Models\BoothSetting;
use App\Models\PhotoboothSession;

/**
 * Generate QRIS pembayaran secara dinamis (REAL & scannable).
 *
 * Dua mode:
 *  1. 'upload'   -> pakai gambar QRIS statis yang diupload admin (default).
 *  2. 'dynamic'  -> dari "Raw QRIS String" milik admin (dari bank/e-wallet-nya),
 *                   kita sisipkan nominal (field 54) + hitung ulang CRC16 sehingga
 *                   menjadi QRIS Dinamis bernilai pasti. Bisa di-scan semua aplikasi
 *                   e-wallet / m-banking yang support QRIS.
 *
 * Mode 'dynamic' juga mendukung PSP pihak ke-3 (Midtrans/Xendit) bila API key diisi,
 * agar status pembayaran otomatis terkonfirmasi via webhook.
 */
class QrisService
{
    /**
     * Mengembalikan URL gambar QR (PNG) yang akan di-scan pengunjung.
     */
    public function getQrImageUrl(PhotoboothSession $session, BoothSetting $setting = null): ?string
    {
        $setting = $setting ?: BoothSetting::getActiveSettings();

        if ($session->payment_method !== 'QRIS') {
            return null;
        }

        // Mode dynamic dengan PSP pihak ke-3 (Midtrans/Xendit) bila dikonfigurasi
        if ($setting->qris_mode === 'dynamic' && $setting->qris_provider && $setting->qris_api_key) {
            $psp = $this->createPspQris($session, $setting);
            if ($psp) {
                return $this->qrImageUrl($psp);
            }
        }

        // Mode dynamic dari raw QRIS string milik admin (REAL & scannable)
        if ($setting->qris_mode === 'dynamic' && !empty($setting->qris_merchant_string)) {
            $dynamic = $this->generateDynamicQris($setting->qris_merchant_string, (int) $session->amount);
            if ($dynamic) {
                return $this->qrImageUrl($dynamic);
            }
        }

        // Fallback: gambar QRIS statis yang diupload admin
        if ($setting->qris_image_path) {
            return asset($setting->qris_image_path);
        }

        // Fallback terakhir: QR dummy berisi info order (bukan pembayaran nyata)
        $dummy = "PHOTOBOOTH-ORDER:{$session->order_id}:AMOUNT:{$session->amount}";
        return $this->qrImageUrl($dummy);
    }

    /**
     * Mengembalikan raw string QRIS (untuk keperluan debugging / webhook).
     */
    public function getQrString(PhotoboothSession $session, BoothSetting $setting = null): ?string
    {
        $setting = $setting ?: BoothSetting::getActiveSettings();
        if ($session->payment_method !== 'QRIS') {
            return null;
        }
        if ($setting->qris_mode === 'dynamic' && !empty($setting->qris_merchant_string)) {
            return $this->generateDynamicQris($setting->qris_merchant_string, (int) $session->amount);
        }
        return null;
    }

    /**
     * Bangun QRIS Dinamis dari raw QRIS string merchant.
     * Menyisipkan nominal ke field ID 54 dan menghitung ulang CRC16 (ISO 13239).
     */
    public function generateDynamicQris(string $merchantQris, int $amount): ?string
    {
        $merchantQris = trim($merchantQris);
        if (empty($merchantQris)) {
            return null;
        }

        // Hapus CRC lama (5 karakter terakhir adalah 4 hex + identifier '5'/'2')
        // QRIS selalu diakhiri dengan field CRC: "6304XXXX"
        $base = preg_replace('/6304[A-Fa-f0-9]{4}$/', '', $merchantQris);
        if (empty($base)) {
            return null;
        }

        // Pastikan tidak ada field 54 ganda -> buang kalau ada
        $base = preg_replace('/540(\d{2})\d+?/', '', $base);

        $amountStr = number_format($amount, 0, '.', ''); // tanpa desimal, tanpa titik
        $field54 = '54' . str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT) . $amountStr;

        $payload = $base . $field54 . '5802ID';
        $crc = $this->crc16($payload);
        return $payload . '6304' . $crc;
    }

    /**
     * CRC16-CCITT (ISO 13239) seperti yang dipakai standar QRIS.
     */
    private function crc16(string $data): string
    {
        $chars = array_map('ord', mb_str_split($data, 1, 'UTF-8'));
        $crc = 0xFFFF;
        foreach ($chars as $c) {
            $crc ^= ($c << 8);
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = ($crc << 1);
                }
                $crc &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Buat URL gambar QR dari sebuah string (pakai api.qrserver.com).
     */
    private function qrImageUrl(string $data): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&margin=8&data=' . urlencode($data);
    }

    /**
     * (Opsional) Buat QRIS via PSP pihak ke-3 — saat ini Midtrans.
     * Mengembalikan raw QRIS string bila berhasil, null bila gagal.
     */
    private function createPspQris(PhotoboothSession $session, BoothSetting $setting): ?string
    {
        if ($setting->qris_provider === 'midtrans') {
            return $this->createMidtransQris($session, $setting);
        }
        if ($setting->qris_provider === 'xendit') {
            return $this->createXenditQris($session, $setting);
        }
        return null;
    }

    private function createMidtransQris(PhotoboothSession $session, BoothSetting $setting): ?string
    {
        try {
            $isSandbox = str_contains($setting->qris_api_key, 'SB-Mid');
            $base = $isSandbox ? 'https://api.sandbox.midtrans.com' : 'https://api.midtrans.com';
            $payload = json_encode([
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $session->order_id,
                    'gross_amount' => (int) $session->amount,
                ],
                'qris' => ['acquirer' => 'gopay'],
            ]);
            $ch = curl_init("$base/v2/charge");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Basic ' . base64_encode($setting->qris_api_key . ':'),
                ],
            ]);
            $res = curl_exec($ch);
            curl_close($ch);
            if (!$res) {
                return null;
            }
            $json = json_decode($res, true);
            foreach ($json['actions'] ?? [] as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    return $action['url'] ?? null;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    private function createXenditQris(PhotoboothSession $session, BoothSetting $setting): ?string
    {
        try {
            $isSandbox = str_contains($setting->qris_api_key, 'xnd_development');
            $base = $isSandbox ? 'https://api.xendit.co' : 'https://api.xendit.co';
            $payload = json_encode([
                'reference_id' => $session->order_id,
                'currency' => 'IDR',
                'amount' => (int) $session->amount,
                'type' => 'DYNAMIC',
            ]);
            $ch = curl_init("$base/qr_codes");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'apikey: ' . $setting->qris_api_key,
                ],
            ]);
            $res = curl_exec($ch);
            curl_close($ch);
            if (!$res) {
                return null;
            }
            $json = json_decode($res, true);
            return $json['qr_string'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
