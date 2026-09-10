<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AnnouncementPriority: string implements HasColor, HasLabel
{
    case Rendah = 'rendah';
    case Normal = 'normal';
    case Tinggi = 'tinggi';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Rendah => 'gray',
            self::Normal => 'info',
            self::Tinggi => 'danger',
        };
    }
}
