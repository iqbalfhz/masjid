<?php

namespace Database\Factories;

use App\Enums\GalleryItemType;
use App\Models\GalleryAlbum;
use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    protected $model = GalleryItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gallery_album_id' => GalleryAlbum::factory(),
            'type' => GalleryItemType::Image,
            'file_path' => 'galeri/'.fake()->uuid().'.jpg',
            'caption' => fake()->sentence(5),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function video(): static
    {
        return $this->state(fn (): array => [
            'type' => GalleryItemType::Video,
            'file_path' => null,
            'external_url' => 'https://www.youtube.com/watch?v='.fake()->lexify('???????????'),
        ]);
    }
}
