<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\PhotoboothSession;

class CleanupExpiredPhotos extends Command
{
    protected $signature = 'photobooth:cleanup';
    protected $description = 'Hapus foto lokal & record sesi yang sudah kedaluwarsa (>3 hari). File di Google Drive tetap tersimpan sebagai arsip.';

    public function handle(): int
    {
        $expired = PhotoboothSession::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $session) {
            $path = public_path($session->result_image_path);
            if ($session->result_image_path && File::exists($path)) {
                File::delete($path);
            }
            $session->delete();
            $count++;
        }

        $this->info("Cleanup selesai: $count sesi kedaluwarsa dihapus (file lokal + record DB). File di Google Drive tetap ada.");

        return self::SUCCESS;
    }
}
