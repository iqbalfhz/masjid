<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GalleryItemType: string implements HasLabel
{
    case Image = 'image';
    case Video = 'video';

    public function getLabel(): string
    {
        return match ($this) {
            self::Image => 'Foto',
            self::Video => 'Video',
        };
    }
}
