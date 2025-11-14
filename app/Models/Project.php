<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'investment',
        'return',
        'roi',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'investment' => 'decimal:2',
        'return' => 'decimal:2',
        'roi' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
