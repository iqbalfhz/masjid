<?php

namespace Database\Factories;

use App\Enums\ModerationStatus;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'message' => fake()->paragraph(),
            'status' => ModerationStatus::Menunggu,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => ModerationStatus::Disetujui,
            'moderated_by' => User::factory(),
            'moderated_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => ModerationStatus::Ditolak,
            'moderated_by' => User::factory(),
            'moderated_at' => now(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (): array => ['name' => null]);
    }
}
