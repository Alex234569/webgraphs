<?php

namespace App\Console\Commands;

use App\Jobs\RebuildMetricsForMonthJob;
use App\Services\MetricsRebuildService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RebuildMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:rebuild 
                            {--month= : The month to rebuild (YYYY-MM)} 
                            {--from= : The start month (YYYY-MM)} 
                            {--to= : The end month (YYYY-MM)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild monthly summary metrics via background jobs';

    /**
     * Execute the console command.
     *
     * @param MetricsRebuildService $service
     * @return int
     */
    public function handle(MetricsRebuildService $service): int
    {
        $month = $this->option('month');
        $from = $this->option('from');
        $to = $this->option('to');

        if ($month) {
            $date = $this->parseDate($month);
            if (!$date) {
                return 1;
            }
            RebuildMetricsForMonthJob::dispatch($date->year, $date->month);
            $this->info("Dispatched rebuild job for {$month}");
            return 0;
        }

        if ($from && $to) {
            $fromDate = $this->parseDate($from);
            $toDate = $this->parseDate($to);

            if (!$fromDate || !$toDate) {
                return 1;
            }

            if ($fromDate > $toDate) {
                $this->error("'from' date must be before or equal to 'to' date.");
                return 1;
            }

            $service->rebuildRange($fromDate, $toDate);
            $this->info("Dispatched rebuild jobs from {$from} to {$to}");
            return 0;
        }

        $this->error("Please provide either --month or both --from and --to options.");
        return 1;
    }

    /**
     * @param string $value
     * @return Carbon|null
     */
    private function parseDate(string $value): ?Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        } catch (\Exception $e) {
            $this->error("Invalid date format: {$value}. Use YYYY-MM.");
            return null;
        }
    }
}
