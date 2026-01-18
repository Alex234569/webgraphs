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
        Schema::create('budget_monthly_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('category');
            $table->decimal('planned_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2);
            $table->decimal('delta_amount', 15, 2);
            $table->decimal('delta_pct', 7, 2)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['year', 'month', 'category'], 'budget_month_year_cat_unique');
            $table->index(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_monthly_summary');
    }
};
