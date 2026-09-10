<?php

namespace Database\Factories;

use App\Models\Rsvp;
use App\Models\Study;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rsvp>
 */
class RsvpFactory extends Factory
{
    protected $model = Rsvp::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rsvpable_type' => Study::class,
            'rsvpable_id' => Study::factory()->approved()->withRsvp(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'person_count' => fake()->numberBetween(1, 4),
        ];
    }
}
