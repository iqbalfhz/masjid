<?php

namespace Database\Factories;

use App\Enums\AnnouncementPriority;
use App\Enums\ContentStatus;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(3, true),
            'start_date' => today(),
            'end_date' => today()->addWeeks(2),
            'priority' => AnnouncementPriority::Normal,
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

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Ditolak,
            'approval_note' => fake()->sentence(),
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'start_date' => today()->subMonth(),
            'end_date' => today()->subWeek(),
        ]);
    }
}
