<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Role bawaan sistem beserta level hierarkinya.
 *
 * Level yang lebih kecil berarti wewenang lebih tinggi; dipakai untuk
 * membatasi role apa saja yang boleh dikelola seorang user (lihat PRD 5.2.15).
 */
enum UserRole: string implements HasLabel
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case KetuaDkm = 'ketua_dkm';
    case Sekretaris = 'sekretaris';
    case Bendahara = 'bendahara';

    public function getLabel(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::Admin => 'Admin (Pengurus Masjid)',
            self::KetuaDkm => 'Ketua DKM',
            self::Sekretaris => 'Sekretaris',
            self::Bendahara => 'Bendahara',
        };
    }

    public function level(): int
    {
        return match ($this) {
            self::Superadmin => 1,
            self::Admin => 2,
            self::KetuaDkm => 3,
            self::Sekretaris => 4,
            self::Bendahara => 4,
        };
    }
}
