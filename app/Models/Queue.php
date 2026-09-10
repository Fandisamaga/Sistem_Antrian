<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    protected $fillable = [
        'queue_number',
        'queue_date',
        'meja_id',
        'layanan_id',
        'operator_id',
        'status',
        'called_at',
        'completed_at',
        'wait_duration',
        'serve_duration',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'completed_at' => 'datetime',
            'wait_duration' => 'integer',
            'serve_duration' => 'integer',
        ];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(Meja::class);
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public static function formatSeconds(?int $seconds): string
    {
        if ($seconds === null || $seconds < 0) {
            return '-';
        }

        $m = floor($seconds / 60);
        $s = $seconds % 60;

        if ($m > 0) {
            return "{$m}m {$s}s";
        }

        return "{$s}s";
    }

    public function getFormattedWaitDurationAttribute(): string
    {
        return self::formatSeconds($this->wait_duration);
    }

    public function getFormattedServeDurationAttribute(): string
    {
        return self::formatSeconds($this->serve_duration);
    }
}
