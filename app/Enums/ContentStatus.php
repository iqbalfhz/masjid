<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ContentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case MenungguApproval = 'menunggu_approval';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::MenungguApproval => 'Menunggu Approval',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::MenungguApproval => 'warning',
            self::Disetujui => 'success',
            self::Ditolak => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::MenungguApproval => 'heroicon-o-clock',
            self::Disetujui => 'heroicon-o-check-circle',
            self::Ditolak => 'heroicon-o-x-circle',
        };
    }
}
