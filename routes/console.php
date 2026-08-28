<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bersihkan foto lokal & record sesi yang sudah >3 hari secara otomatis setiap hari.
Schedule::command('photobooth:cleanup')->daily();
