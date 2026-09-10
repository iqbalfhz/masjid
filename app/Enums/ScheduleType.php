<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ScheduleType: string implements HasLabel
{
    case Rutin = 'rutin';
    case Insidental = 'insidental';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rutin => 'Rutin (mingguan)',
            self::Insidental => 'Insidental (sekali)',
        };
    }
}
