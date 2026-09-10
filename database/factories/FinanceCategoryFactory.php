<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\FinanceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinanceCategory>
 */
class FinanceCategoryFactory extends Factory
{
    protected $model = FinanceCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(TransactionType::cases()),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (): array => ['type' => TransactionType::In]);
    }

    public function expense(): static
    {
        return $this->state(fn (): array => ['type' => TransactionType::Out]);
    }
}
