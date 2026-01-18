<?php

namespace App\Services;

use App\Models\FinanceMonthlySummary;
use App\Models\BudgetMonthlySummary;
use App\Models\Revenue;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportService
{
    /**
     * Получить название месяца на русском
     */
    public function getRussianMonthName(int $month): string
    {
        return [
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        ][$month] ?? (string)$month;
    }

    /**
     * Получить данные для ежемесячного финансового отчета
     */
    public function getMonthlySummaryData(?string $from, ?string $to): Collection
    {
        $query = FinanceMonthlySummary::orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        if ($from) {
            $fromDate = Carbon::parse($from);
            $query->where(function ($q) use ($fromDate) {
                $q->where('year', '>', $fromDate->year)
                    ->orWhere(function ($sq) use ($fromDate) {
                        $sq->where('year', '=', $fromDate->year)
                            ->where('month', '>=', $fromDate->month);
                    });
            });
        }

        if ($to) {
            $toDate = Carbon::parse($to);
            $query->where(function ($q) use ($toDate) {
                $q->where('year', '<', $toDate->year)
                    ->orWhere(function ($sq) use ($toDate) {
                        $sq->where('year', '=', $toDate->year)
                            ->where('month', '<=', $toDate->month);
                    });
            });
        }

        return $query->get();
    }

    /**
     * Получить данные для отчета План vs Факт
     */
    public function getBudgetPlanFactData(?string $from, ?string $to): Collection
    {
        $query = BudgetMonthlySummary::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('category');

        if ($from) {
            $fromDate = Carbon::parse($from);
            $query->where(function ($q) use ($fromDate) {
                $q->where('year', '>', $fromDate->year)
                    ->orWhere(function ($sq) use ($fromDate) {
                        $sq->where('year', '=', $fromDate->year)
                            ->where('month', '>=', $fromDate->month);
                    });
            });
        }

        if ($to) {
            $toDate = Carbon::parse($to);
            $query->where(function ($q) use ($toDate) {
                $q->where('year', '<', $toDate->year)
                    ->orWhere(function ($sq) use ($toDate) {
                        $sq->where('year', '=', $toDate->year)
                            ->where('month', '<=', $toDate->month);
                    });
            });
        }

        return $query->get();
    }

    /**
     * Получить детальные операции по доходам или расходам
     */
    public function getOperationsData(string $type, ?string $from, ?string $to): Collection
    {
        $query = $type === 'expenses' ? Expense::query() : Revenue::query();
        $query->orderBy('date', 'desc');

        if ($from) {
            $query->where('date', '>=', $from);
        }

        if ($to) {
            $query->where('date', '<=', $to);
        }

        return $query->get();
    }

    /**
     * Генерация CSV контента из коллекции данных
     */
    public function generateCsv(array $headers, Collection $data, callable $rowMapper): string
    {
        $handle = fopen('php://temp', 'r+');

        // Добавляем UTF-8 BOM для корректного открытия в Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($handle, $headers, ';');

        foreach ($data as $item) {
            fputcsv($handle, $rowMapper($item), ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    /**
     * Генерация XLSX контента из коллекции данных
     */
    public function generateXlsx(array $headers, Collection $data, callable $rowMapper): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовки
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1) . '1', $header);
        }

        // Данные
        $row = 2;
        foreach ($data as $item) {
            $rowData = $rowMapper($item);
            foreach ($rowData as $colIndex => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex + 1) . $row, $value);
            }
            $row++;
        }

        // Авто-ширина колонок
        foreach (range(1, count($headers)) as $col) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $handle = fopen('php://temp', 'r+');
        $writer->save($handle);
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }
}
