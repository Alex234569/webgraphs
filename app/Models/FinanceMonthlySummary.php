<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceMonthlySummary extends Model
{
    protected $table = 'finance_monthly_summary';

    protected $fillable = [
        'year',
        'month',
        'revenue_total',
        'expense_total',
        'profit_total',
        'profit_margin_pct',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
        'revenue_total' => 'decimal:2',
        'expense_total' => 'decimal:2',
        'profit_total' => 'decimal:2',
        'profit_margin_pct' => 'decimal:2',
    ];
}
