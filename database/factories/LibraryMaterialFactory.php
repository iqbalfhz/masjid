<?php

namespace Database\Factories;

use App\Enums\MaterialType;
use App\Models\LibraryMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryMaterial>
 */
class LibraryMaterialFactory extends Factory
{
    protected $model = LibraryMaterial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Materi '.fake()->sentence(3),
            'type' => MaterialType::Pdf,
            'file_path' => 'e-library/'.fake()->uuid().'.pdf',
            'ustadz_name' => 'Ustadz '.fake()->name('male'),
            'material_date' => today()->subDays(fake()->numberBetween(1, 120)),
            'description' => fake()->sentence(12),
            'created_by' => User::factory(),
        ];
    }

    public function video(): static
    {
        return $this->state(fn (): array => [
            'type' => MaterialType::Video,
            'file_path' => null,
            'external_url' => 'https://www.youtube.com/watch?v='.fake()->lexify('???????????'),
        ]);
    }
}
