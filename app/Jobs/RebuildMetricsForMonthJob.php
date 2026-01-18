<?php

namespace App\Jobs;

use App\Services\MetricsRebuildService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildMetricsForMonthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;
    protected $month;

    /**
     * Create a new job instance.
     *
     * @param int $year
     * @param int $month
     */
    public function __construct(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * Execute the job.
     *
     * @param MetricsRebuildService $service
     * @return void
     */
    public function handle(MetricsRebuildService $service): void
    {
        $service->rebuildForMonth($this->year, $this->month);
    }
}
