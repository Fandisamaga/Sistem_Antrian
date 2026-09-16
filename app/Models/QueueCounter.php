<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueCounter extends Model
{
    protected $fillable = [
        'meja_id',
        'queue_date',
        'current_number',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'current_number' => 'integer',
        ];
    }

    public $timestamps = false;
}
