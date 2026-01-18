<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetMonthlySummary extends Model
{
    protected $table = 'budget_monthly_summary';

    protected $fillable = [
        'year',
        'month',
        'category',
        'planned_amount',
        'actual_amount',
        'delta_amount',
        'delta_pct',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'delta_amount' => 'decimal:2',
        'delta_pct' => 'decimal:2',
    ];
}
