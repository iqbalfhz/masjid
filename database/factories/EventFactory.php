<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'event_date' => today()->addDays(fake()->numberBetween(1, 30)),
            'start_time' => '08:00:00',
            'end_time' => '11:00:00',
            'location' => 'Masjid An-Nur, Lantai P3a Tangcity Mall',
            'category' => fake()->randomElement(['Sosial', 'Ramadhan', 'Anak & Remaja', 'Hari Besar']),
            'rsvp_enabled' => false,
            'status' => ContentStatus::Draft,
            'created_by' => User::factory(),
        ];
    }

    public function awaitingApproval(): static
    {
        return $this->state(fn (): array => ['status' => ContentStatus::MenungguApproval]);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Disetujui,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (): array => ['event_date' => today()->subDays(fake()->numberBetween(1, 60))]);
    }

    public function withRsvp(?int $quota = null): static
    {
        return $this->state(fn (): array => [
            'rsvp_enabled' => true,
            'rsvp_quota' => $quota,
        ]);
    }
}
