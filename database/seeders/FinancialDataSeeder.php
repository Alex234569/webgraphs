<?php

namespace Database\Seeders;

use App\Models\Revenue;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FinancialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('ru_RU');

        // Генерация доходов за последние 12 месяцев
        for ($month = 11; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month);

            // 3-5 записей доходов в месяц
            for ($i = 0; $i < rand(3, 5); $i++) {
                Revenue::create([
                    'amount' => $faker->randomFloat(2, 50000, 300000),
                    'date' => $date->copy()->addDays(rand(1, 28)),
                    'description' => $faker->sentence(6),
                    'category' => $faker->randomElement(['Продажи', 'Услуги', 'Консультации', 'Лицензии']),
                ]);
            }
        }

        // Генерация расходов за последние 12 месяцев
        $expenseCategories = ['Зарплаты', 'Аренда', 'Маркетинг', 'IT', 'Прочее'];

        for ($month = 11; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month);

            foreach ($expenseCategories as $category) {
                // 2-3 записи в категории в месяц
                for ($i = 0; $i < rand(2, 3); $i++) {
                    $amount = match($category) {
                        'Зарплаты' => $faker->randomFloat(2, 80000, 150000),
                        'Аренда' => $faker->randomFloat(2, 20000, 40000),
                        'Маркетинг' => $faker->randomFloat(2, 30000, 80000),
                        'IT' => $faker->randomFloat(2, 15000, 50000),
                        'Прочее' => $faker->randomFloat(2, 5000, 25000),
                    };

                    Expense::create([
                        'amount' => $amount,
                        'date' => $date->copy()->addDays(rand(1, 28)),
                        'description' => $faker->sentence(5),
                        'category' => $category,
                    ]);
                }
            }
        }

        // Генерация бюджетов за последние 12 месяцев
        for ($month = 11; $month >= 0; $month--) {
            $date = Carbon::now()->subMonths($month);

            foreach ($expenseCategories as $category) {
                $planned = match($category) {
                    'Зарплаты' => $faker->randomFloat(2, 250000, 350000),
                    'Аренда' => $faker->randomFloat(2, 70000, 100000),
                    'Маркетинг' => $faker->randomFloat(2, 100000, 200000),
                    'IT' => $faker->randomFloat(2, 50000, 120000),
                    'Прочее' => $faker->randomFloat(2, 30000, 60000),
                };

                // Фактическая сумма с отклонением -10% до +15% от плана
                $variance = $faker->randomFloat(2, 0.90, 1.15);
                $actual = $planned * $variance;

                Budget::create([
                    'category' => $category,
                    'planned_amount' => $planned,
                    'actual_amount' => $actual,
                    'year' => $date->year,
                    'month' => $date->month,
                ]);
            }
        }

        // Генерация проектов
        $projectNames = [
            'Модернизация IT инфраструктуры',
            'Запуск нового продукта',
            'Расширение офиса',
            'Маркетинговая кампания Q4',
            'Автоматизация процессов',
            'Обучение персонала',
            'Разработка мобильного приложения',
            'Редизайн веб-сайта',
        ];

        foreach ($projectNames as $index => $name) {
            $investment = $faker->randomFloat(2, 500000, 2000000);
            $returnAmount = $investment * $faker->randomFloat(2, 0.8, 1.8);
            $roi = (($returnAmount - $investment) / $investment) * 100;

            $startDate = Carbon::now()->subMonths(rand(1, 12));
            $status = $faker->randomElement(['active', 'active', 'completed', 'completed']);
            $endDate = $status === 'completed'
                ? $startDate->copy()->addMonths(rand(3, 9))
                : null;

            Project::create([
                'name' => $name,
                'investment' => $investment,
                'return' => $returnAmount,
                'roi' => $roi,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
            ]);
        }
    }
}
