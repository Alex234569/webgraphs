<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use App\Models\Expense;
use App\Models\Budget;
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
        $months = $request->input('months', 12);

        $data = Revenue::selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->where('date', '>=', Carbon::now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $data->map(function ($item) {
            Carbon::setLocale('ru');
            return Carbon::parse($item->month)->translatedFormat('M Y');
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
        $months = $request->input('months', 12);

        $data = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->where('date', '>=', Carbon::now()->subMonths($months))
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
        $months = $request->input('months', 6);

        $revenues = Revenue::selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->where('date', '>=', Carbon::now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $expenses = Expense::selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->where('date', '>=', Carbon::now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthsList = collect();
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthsList->push($month);
        }

        Carbon::setLocale('ru');
        $labels = $monthsList->map(fn($m) => Carbon::parse($m)->translatedFormat('M Y'));
        $profitData = $monthsList->map(function ($month) use ($revenues, $expenses) {
            $revenue = $revenues->get($month)->total ?? 0;
            $expense = $expenses->get($month)->total ?? 0;
            return $revenue - $expense;
        });

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
        $months = Budget::selectRaw('DISTINCT year, month')
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

            $data = Budget::select('category', 'planned_amount', 'actual_amount')
                ->where('year', $year)
                ->where('month', $month)
                ->get();

            return response()->json([
                'categories' => $data->pluck('category'),
                'planned' => $data->pluck('planned_amount'),
                'actual' => $data->pluck('actual_amount'),
            ]);
        }

        // Иначе показываем за период (для обратной совместимости)
        $months = $request->input('months', 6);
        $startDate = Carbon::now()->subMonths($months);

        $data = Budget::select('category', DB::raw('SUM(planned_amount) as total_planned'), DB::raw('SUM(actual_amount) as total_actual'))
            ->where('year', '>=', $startDate->year)
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
