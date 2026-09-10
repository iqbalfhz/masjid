<?php

namespace App\Models;

use App\Enums\MaterialType;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\RecordsActivity;
use App\Models\Concerns\TracksCreator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

#[Fillable(['title', 'slug', 'type', 'file_path', 'external_url', 'study_id', 'ustadz_name', 'material_date', 'description', 'downloads', 'created_by'])]
class LibraryMaterial extends Model
{
    use HasFactory, HasSlug, RecordsActivity, Searchable, TracksCreator;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MaterialType::class,
            'material_date' => 'date',
            'downloads' => 'integer',
        ];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Materi bisa berupa file yang diunggah atau tautan eksternal (YouTube, Drive).
     */
    public function url(): ?string
    {
        if (filled($this->external_url)) {
            return $this->external_url;
        }

        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'ustadz_name' => (string) $this->ustadz_name,
            'description' => strip_tags((string) $this->description),
        ];
    }
}
