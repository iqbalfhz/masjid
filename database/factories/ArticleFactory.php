<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'excerpt' => fake()->sentence(15),
            'content' => fake()->paragraphs(5, true),
            'article_category_id' => ArticleCategory::factory(),
            'publish_date' => today(),
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

    /**
     * Sudah disetujui tapi tanggal tayangnya belum tiba, jadi belum boleh publik.
     */
    public function scheduled(): static
    {
        return $this->approved()->state(fn (): array => ['publish_date' => today()->addWeek()]);
    }
}
