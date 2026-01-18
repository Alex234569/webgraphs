<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategoryMonthlySummary extends Model
{
    protected $table = 'expense_category_monthly_summary';

    protected $fillable = [
        'year',
        'month',
        'category',
        'expense_total',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
        'expense_total' => 'decimal:2',
    ];
}
