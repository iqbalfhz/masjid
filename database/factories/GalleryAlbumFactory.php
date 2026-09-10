<?php

namespace Database\Factories;

use App\Models\GalleryAlbum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryAlbum>
 */
class GalleryAlbumFactory extends Factory
{
    protected $model = GalleryAlbum::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(['Kajian', 'Ramadhan', 'Sosial', 'Renovasi']),
            'event_date' => today()->subDays(fake()->numberBetween(1, 200)),
            'description' => fake()->sentence(12),
            'is_published' => true,
            'created_by' => User::factory(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
