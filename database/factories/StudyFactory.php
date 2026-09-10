<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\ScheduleType;
use App\Models\Study;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Study>
 */
class StudyFactory extends Factory
{
    protected $model = Study::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'theme' => 'Kajian '.fake()->words(3, true),
            'ustadz_name' => 'Ustadz '.fake()->name('male'),
            'schedule_type' => ScheduleType::Rutin,
            'day_of_week' => fake()->numberBetween(0, 6),
            'time' => '19:30:00',
            'end_time' => '21:00:00',
            'location' => 'Masjid An-Nur, Lantai P3a Tangcity Mall',
            'description' => fake()->paragraph(),
            'rsvp_enabled' => false,
            'status' => ContentStatus::Draft,
            'created_by' => User::factory(),
        ];
    }

    public function incidental(): static
    {
        return $this->state(fn (): array => [
            'schedule_type' => ScheduleType::Insidental,
            'day_of_week' => null,
            'start_date' => today()->addWeek(),
        ]);
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

    public function withRsvp(?int $quota = null): static
    {
        return $this->state(fn (): array => [
            'rsvp_enabled' => true,
            'rsvp_quota' => $quota,
        ]);
    }
}
