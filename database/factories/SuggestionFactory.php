<?php

namespace Database\Factories;

use App\Enums\SuggestionCategory;
use App\Enums\SuggestionStatus;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Suggestion>
 */
class SuggestionFactory extends Factory
{
    protected $model = Suggestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'contact' => fake()->phoneNumber(),
            'category' => fake()->randomElement(SuggestionCategory::cases()),
            'message' => fake()->paragraph(),
            'status' => SuggestionStatus::Baru,
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn (): array => ['name' => null, 'contact' => null]);
    }

    public function handled(): static
    {
        return $this->state(fn (): array => [
            'status' => SuggestionStatus::Selesai,
            'response_note' => fake()->sentence(),
            'handled_by' => User::factory(),
            'handled_at' => now(),
        ]);
    }
}
