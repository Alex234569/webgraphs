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
        Schema::create('expense_category_monthly_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('category');
            $table->decimal('expense_total', 15, 2);
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['year', 'month', 'category'], 'exp_cat_month_year_unique');
            $table->index(['year', 'month']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_category_monthly_summary');
    }
};
