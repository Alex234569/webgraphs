<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('finance_monthly_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('revenue_total', 15, 2);
            $table->decimal('expense_total', 15, 2);
            $table->decimal('profit_total', 15, 2);
            $table->decimal('profit_margin_pct', 7, 2)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['year', 'month']);
            $table->index(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_monthly_summary');
    }
};
