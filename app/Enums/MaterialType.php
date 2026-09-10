<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MaterialType: string implements HasIcon, HasLabel
{
    case Pdf = 'pdf';
    case Slide = 'slide';
    case Audio = 'audio';
    case Video = 'video';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pdf => 'Dokumen PDF',
            self::Slide => 'Slide Presentasi',
            self::Audio => 'Rekaman Audio',
            self::Video => 'Rekaman Video',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pdf => 'heroicon-o-document-text',
            self::Slide => 'heroicon-o-presentation-chart-bar',
            self::Audio => 'heroicon-o-musical-note',
            self::Video => 'heroicon-o-video-camera',
        };
    }
}
