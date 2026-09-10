<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SuggestionCategory: string implements HasLabel
{
    case Fasilitas = 'fasilitas';
    case Kegiatan = 'kegiatan';
    case Keuangan = 'keuangan';
    case Lainnya = 'lainnya';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}
