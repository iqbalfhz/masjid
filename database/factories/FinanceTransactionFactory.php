<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinanceTransaction>
 */
class FinanceTransactionFactory extends Factory
{
    protected $model = FinanceTransaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(TransactionType::cases());

        return [
            'date' => today(),
            'type' => $type,
            'finance_category_id' => FinanceCategory::factory()->state(['type' => $type]),
            'amount' => fake()->numberBetween(50, 5000) * 1000,
            'description' => fake()->sentence(4),
            'created_by' => User::factory(),
        ];
    }

    public function income(): static
    {
        return $this->state(fn (): array => [
            'type' => TransactionType::In,
            'finance_category_id' => FinanceCategory::factory()->income(),
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn (): array => [
            'type' => TransactionType::Out,
            'finance_category_id' => FinanceCategory::factory()->expense(),
        ]);
    }
}
