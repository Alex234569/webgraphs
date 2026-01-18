<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinanceMonthlySummary;
use App\Models\ExpenseCategoryMonthlySummary;
use App\Models\BudgetMonthlySummary;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartsController extends Controller
{
    /**
     * Доходы по месяцам
     */
    public function revenue(Request $request)
    {
        $monthsCount = $request->input('months', 12);
        $startDate = Carbon::now()->subMonths($monthsCount);

        $data = FinanceMonthlySummary::selectRaw('year, month, revenue_total as total')
            ->where(function ($query) use ($startDate) {
                $query->where('year', '>', $startDate->year)
                    ->orWhere(function ($q) use ($startDate) {
                        $q->where('year', '=', $startDate->year)
                            ->where('month', '>=', $startDate->month);
                    });
            })
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = $data->map(function ($item) {
            Carbon::setLocale('ru');
            return Carbon::create($item->year, $item->month, 1)->translatedFormat('M Y');
        });

        $values = $data->pluck('total');

        return response()->json([
            'labels' => $labels,
            'data' => $values,
            'total' => $values->sum(),
        ]);
    }

    /**
     * Расходы по категориям
     */
    public function expenses(Request $request)
    {
        $monthsCount = $request->input('months', 12);
        $startDate = Carbon::now()->subMonths($monthsCount);

        $data = ExpenseCategoryMonthlySummary::select('category', DB::raw('SUM(expense_total) as total'))
            ->where(function ($query) use ($startDate) {
                $query->where('year', '>', $startDate->year)
                    ->orWhere(function ($q) use ($startDate) {
                        $q->where('year', '=', $startDate->year)
                            ->where('month', '>=', $startDate->month);
                    });
            })
            ->groupBy('category')
            ->get();

        return response()->json([
            'categories' => $data->pluck('category'),
            'data' => $data->pluck('total'),
            'total' => $data->sum('total'),
        ]);
    }

    /**
     * Прибыль по месяцам
     */
    public function profit(Request $request)
    {
        $monthsCount = $request->input('months', 6);
        $startDate = Carbon::now()->subMonths($monthsCount);

        $data = FinanceMonthlySummary::select('year', 'month', 'profit_total')
            ->where(function ($query) use ($startDate) {
                $query->where('year', '>', $startDate->year)
                    ->orWhere(function ($q) use ($startDate) {
                        $q->where('year', '=', $startDate->year)
                            ->where('month', '>=', $startDate->month);
                    });
            })
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        Carbon::setLocale('ru');
        $labels = $data->map(fn($item) => Carbon::create($item->year, $item->month, 1)->translatedFormat('M Y'));
        $profitData = $data->pluck('profit_total');

        return response()->json([
            'labels' => $labels,
            'data' => $profitData,
        ]);
    }

    /**
     * Получить доступные месяцы с бюджетными данными
     */
    public function availableBudgetMonths()
    {
        $months = BudgetMonthlySummary::selectRaw('DISTINCT year, month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                Carbon::setLocale('ru');
                $date = Carbon::create($item->year, $item->month, 1);
                $monthName = $date->translatedFormat('F');
                $capitalizedMonth = mb_strtoupper(mb_substr($monthName, 0, 1)) . mb_substr($monthName, 1);

                return [
                    'value' => [
                        'year' => $item->year,
                        'month' => $item->month,
                    ],
                    'label' => "{$capitalizedMonth} {$item->year}",
                ];
            });

        return response()->json($months);
    }

    /**
     * Бюджет vs Факт за конкретный месяц - ТОЛЬКО ДЛЯ АДМИНА
     */
    public function budgetVsFact(Request $request)
    {
        // Если переданы year и month - показываем конкретный месяц
        if ($request->has('year') && $request->has('month')) {
            $year = $request->input('year');
            $month = $request->input('month');

            $data = BudgetMonthlySummary::select('category', 'planned_amount', 'actual_amount')
                ->where('year', $year)
                ->where('month', $month)
                ->get();

            return response()->json([
                'categories' => $data->pluck('category'),
                'planned' => $data->pluck('planned_amount'),
                'actual' => $data->pluck('actual_amount'),
            ]);
        }

        // Иначе показываем за период
        $monthsCount = $request->input('months', 6);
        $startDate = Carbon::now()->subMonths($monthsCount);

        $data = BudgetMonthlySummary::select('category', DB::raw('SUM(planned_amount) as total_planned'), DB::raw('SUM(actual_amount) as total_actual'))
            ->where(function ($query) use ($startDate) {
                $query->where('year', '>', $startDate->year)
                    ->orWhere(function ($q) use ($startDate) {
                        $q->where('year', '=', $startDate->year)
                            ->where('month', '>=', $startDate->month);
                    });
            })
            ->groupBy('category')
            ->get();

        return response()->json([
            'categories' => $data->pluck('category'),
            'planned' => $data->pluck('total_planned'),
            'actual' => $data->pluck('total_actual'),
        ]);
    }

    /**
     * ROI по проектам - ТОЛЬКО ДЛЯ АДМИНА
     */
    public function roi()
    {
        $projects = Project::select('name', 'investment', 'return', 'roi', 'status')
            ->orderBy('roi', 'desc')
            ->get();

        return response()->json([
            'projects' => $projects->pluck('name'),
            'roi' => $projects->pluck('roi'),
            'investment' => $projects->pluck('investment'),
            'return' => $projects->pluck('return'),
            'status' => $projects->pluck('status'),
        ]);
    }
}
