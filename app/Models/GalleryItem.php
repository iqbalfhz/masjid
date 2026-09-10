<?php

namespace App\Models;

use App\Enums\GalleryItemType;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['gallery_album_id', 'type', 'file_path', 'external_url', 'caption', 'sort_order'])]
class GalleryItem extends Model
{
    use HasFactory, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GalleryItemType::class,
            'sort_order' => 'integer',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function url(): ?string
    {
        if (filled($this->external_url)) {
            return $this->external_url;
        }

        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}
