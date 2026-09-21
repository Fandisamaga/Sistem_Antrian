<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueCounter extends Model
{
    /** @var string Format sesuai kolom DATE agar pencarian counter konsisten di semua driver database. */
    protected $dateFormat = 'Y-m-d';

    protected $fillable = [
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
