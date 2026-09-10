<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Meja extends Model
{
    protected $fillable = [
        'nomor_meja',
        'nama_meja',
        'layanan_id',
    ];

    protected function casts(): array
    {
        return [
            'nomor_meja' => 'integer',
        ];
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function getFormattedNomorAttribute(): string
    {
        return str_pad((string) ($this->nomor_meja ?: $this->id), 2, '0', STR_PAD_LEFT);
    }
}
