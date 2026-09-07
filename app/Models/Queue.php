<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    protected $fillable = ['queue_number', 'meja_id', 'status', 'call_count'];

    public function meja(): BelongsTo
    {
        return $this->belongsTo(Meja::class);
    }
}
