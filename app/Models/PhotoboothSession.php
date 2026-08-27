<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PhotoboothSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_token',
        'order_id',
        'package_name',
        'layout_type',
        'amount',
        'payment_status',
        'payment_method',
        'payment_qr_url',
        'duration_minutes',
        'session_started_at',
        'session_expires_at',
        'result_image_path',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'duration_minutes' => 'integer',
        'session_started_at' => 'datetime',
        'session_expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function isSessionActive(): bool
    {
        if ($this->payment_status !== 'paid' || !$this->session_expires_at) {
            return false;
        }

        return Carbon::now()->lessThanOrEqualTo($this->session_expires_at);
    }

    public function getRemainingSeconds(): int
    {
        if (!$this->isSessionActive()) {
            return 0;
        }

        return max(0, Carbon::now()->diffInSeconds($this->session_expires_at, false));
    }

    public function startSession(): void
    {
        $now = Carbon::now();
        $this->update([
            'payment_status' => 'paid',
            'session_started_at' => $now,
            'session_expires_at' => $now->copy()->addMinutes($this->duration_minutes),
        ]);
    }
}