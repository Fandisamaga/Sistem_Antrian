<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    protected $fillable = [
        'nama_layanan',
        'kode_layanan',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function mejas(): HasMany
    {
        return $this->hasMany(Meja::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}

