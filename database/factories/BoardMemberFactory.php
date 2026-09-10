<?php

namespace Database\Factories;

use App\Models\BoardMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardMember>
 */
class BoardMemberFactory extends Factory
{
    protected $model = BoardMember::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position' => fake()->randomElement(['Ketua DKM', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Bidang Dakwah']),
            'bio' => fake()->sentence(12),
            'period_start' => now()->year,
            'period_end' => now()->year + 3,
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
