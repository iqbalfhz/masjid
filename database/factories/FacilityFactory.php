<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Ruang '.fake()->unique()->word(),
            'description' => fake()->sentence(),
            'capacity' => fake()->numberBetween(15, 300),
            'is_active' => true,
        ];
    }
}
