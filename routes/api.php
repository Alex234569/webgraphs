<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChartsController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth');

// Графики - доступны всем авторизованным пользователям
Route::middleware('auth')->group(function () {
    Route::get('/charts/revenue', [ChartsController::class, 'revenue']);
    Route::get('/charts/expenses', [ChartsController::class, 'expenses']);
    Route::get('/charts/profit', [ChartsController::class, 'profit']);
});

// Графики - доступны только администраторам
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/charts/budget-vs-fact', [ChartsController::class, 'budgetVsFact']);
    Route::get('/charts/available-budget-months', [ChartsController::class, 'availableBudgetMonths']);
    Route::get('/charts/roi', [ChartsController::class, 'roi']);

    // Отчеты
    Route::get('/reports/monthly-summary', [ReportController::class, 'monthlySummary']);
    Route::get('/reports/monthly-summary/export', [ReportController::class, 'monthlySummaryExport']);
    Route::get('/reports/budget-plan-fact', [ReportController::class, 'budgetPlanFact']);
    Route::get('/reports/budget-plan-fact/export', [ReportController::class, 'budgetPlanFactExport']);
    Route::get('/reports/operations/export', [ReportController::class, 'operationsExport']);
});

