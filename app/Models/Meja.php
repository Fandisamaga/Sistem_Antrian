<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meja extends Model
{
    protected $fillable = ['nama_meja'];

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
