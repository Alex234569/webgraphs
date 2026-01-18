<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetMonthlySummary;
use App\Models\Expense;
use App\Models\ExpenseCategoryMonthlySummary;
use App\Models\FinanceMonthlySummary;
use App\Models\Revenue;
use App\Jobs\RebuildMetricsForMonthJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricsRebuildService
{
    public function rebuildForMonth(int $year, int $month): void
    {
        DB::transaction(function () use ($year, $month) {
            $this->rebuildFinanceSummary($year, $month);
            $this->rebuildExpenseCategorySummary($year, $month);
            $this->rebuildBudgetSummary($year, $month);
        });
    }

    public function rebuildRange(Carbon $from, Carbon $to): void
    {
        $current = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();

        while ($current <= $end) {
            RebuildMetricsForMonthJob::dispatch($current->year, $current->month);
            $current->addMonth();
        }
    }

    private function rebuildFinanceSummary(int $year, int $month): void
    {
        $revenueTotal = Revenue::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $expenseTotal = Expense::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $profitTotal = $revenueTotal - $expenseTotal;
        $profitMarginPct = $revenueTotal > 0 ? ($profitTotal / $revenueTotal) * 100 : null;

        FinanceMonthlySummary::updateOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'revenue_total' => $revenueTotal,
                'expense_total' => $expenseTotal,
                'profit_total' => $profitTotal,
                'profit_margin_pct' => $profitMarginPct,
                'calculated_at' => now(),
            ]
        );
    }

    private function rebuildExpenseCategorySummary(int $year, int $month): void
    {
        $summaries = Expense::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        $activeCategories = $summaries->pluck('category')->toArray();

        // Remove categories that no longer exist for this month
        ExpenseCategoryMonthlySummary::where('year', $year)
            ->where('month', $month)
            ->whereNotIn('category', $activeCategories)
            ->delete();

        foreach ($summaries as $summary) {
            ExpenseCategoryMonthlySummary::updateOrCreate(
                ['year' => $year, 'month' => $month, 'category' => $summary->category],
                [
                    'expense_total' => $summary->total,
                    'calculated_at' => now(),
                ]
            );
        }
    }

    private function rebuildBudgetSummary(int $year, int $month): void
    {
        $budgets = Budget::where('year', $year)
            ->where('month', $month)
            ->get();

        $activeCategories = $budgets->pluck('category')->toArray();

        // Remove budget summaries that no longer have a source budget
        BudgetMonthlySummary::where('year', $year)
            ->where('month', $month)
            ->whereNotIn('category', $activeCategories)
            ->delete();

        foreach ($budgets as $budget) {
            $deltaAmount = $budget->actual_amount - $budget->planned_amount;
            $deltaPct = $budget->planned_amount != 0 ? ($deltaAmount / $budget->planned_amount) * 100 : null;

            BudgetMonthlySummary::updateOrCreate(
                ['year' => $year, 'month' => $month, 'category' => $budget->category],
                [
                    'planned_amount' => $budget->planned_amount,
                    'actual_amount' => $budget->actual_amount,
                    'delta_amount' => $deltaAmount,
                    'delta_pct' => $deltaPct,
                    'calculated_at' => now(),
                ]
            );
        }
    }
}
