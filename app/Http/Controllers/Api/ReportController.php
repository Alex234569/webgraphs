<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Превью ежемесячного финансового отчета
     */
    public function monthlySummary(Request $request)
    {
        $data = $this->reportService->getMonthlySummaryData(
            $request->query('from'),
            $request->query('to')
        );

        return response()->json($data);
    }

    /**
     * Экспорт ежемесячного финансового отчета
     */
    public function monthlySummaryExport(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $format = $request->query('format', 'csv');
        $data = $this->reportService->getMonthlySummaryData($from, $to);

        $headers = ['Год', 'Месяц', 'Доход', 'Расход', 'Прибыль', 'Маржа %'];
        $rowMapper = function ($item) {
            return [
                $item->year,
                $this->reportService->getRussianMonthName($item->month),
                $item->revenue_total,
                $item->expense_total,
                $item->profit_total,
                $item->profit_margin_pct,
            ];
        };

        if ($format === 'xlsx') {
            $content = $this->reportService->generateXlsx($headers, $data, $rowMapper);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            $extension = 'xlsx';
        } else {
            $content = $this->reportService->generateCsv($headers, $data, $rowMapper);
            $contentType = 'text/csv';
            $extension = 'csv';
        }

        $filename = "monthly-summary_" . ($from ?: 'all') . "_" . ($to ?: 'now') . ".$extension";

        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    /**
     * Превью отчета План vs Факт
     */
    public function budgetPlanFact(Request $request)
    {
        $data = $this->reportService->getBudgetPlanFactData(
            $request->query('from'),
            $request->query('to')
        );

        return response()->json($data);
    }

    /**
     * Экспорт отчета План vs Факт
     */
    public function budgetPlanFactExport(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $format = $request->query('format', 'csv');
        $data = $this->reportService->getBudgetPlanFactData($from, $to);

        $headers = ['Год', 'Месяц', 'Категория', 'План', 'Факт', 'Дельта', 'Дельта %'];
        $rowMapper = function ($item) {
            return [
                $item->year,
                $this->reportService->getRussianMonthName($item->month),
                $item->category,
                $item->planned_amount,
                $item->actual_amount,
                $item->delta_amount,
                $item->delta_pct,
            ];
        };

        if ($format === 'xlsx') {
            $content = $this->reportService->generateXlsx($headers, $data, $rowMapper);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            $extension = 'xlsx';
        } else {
            $content = $this->reportService->generateCsv($headers, $data, $rowMapper);
            $contentType = 'text/csv';
            $extension = 'csv';
        }

        $filename = "budget-plan-fact_" . ($from ?: 'all') . "_" . ($to ?: 'now') . ".$extension";

        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    /**
     * Экспорт детальных операций
     */
    public function operationsExport(Request $request)
    {
        $type = $request->query('type', 'expenses');
        $from = $request->query('from');
        $to = $request->query('to');
        $format = $request->query('format', 'csv');

        $data = $this->reportService->getOperationsData($type, $from, $to);

        if ($type === 'expenses') {
            $headers = ['Дата', 'Категория', 'Сумма', 'Описание'];
            $rowMapper = function ($item) {
                return [$item->date->format('Y-m-d'), $item->category, $item->amount, $item->description];
            };
        } else {
            $headers = ['Дата', 'Сумма', 'Описание'];
            $rowMapper = function ($item) {
                return [$item->date->format('Y-m-d'), $item->amount, $item->description];
            };
        }

        if ($format === 'xlsx') {
            $content = $this->reportService->generateXlsx($headers, $data, $rowMapper);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            $extension = 'xlsx';
        } else {
            $content = $this->reportService->generateCsv($headers, $data, $rowMapper);
            $contentType = 'text/csv';
            $extension = 'csv';
        }

        $filename = "operations_{$type}_" . ($from ?: 'all') . "_" . ($to ?: 'now') . ".$extension";

        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
