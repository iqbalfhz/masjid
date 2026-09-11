<?php

namespace App\Filament\Pages;

use App\Models\Event;
use App\Models\Study;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Kalender overview seluruh jadwal (PRD 5.2.2): kajian rutin, kegiatan, dan
 * peminjaman fasilitas yang sudah disetujui — termasuk yang masih draft,
 * supaya pengurus bisa melihat rencana penuh sebelum publikasi.
 */
class ActivityCalendar extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Konten & Informasi';

    protected static ?string $navigationLabel = 'Kalender Kegiatan';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'kalender';

    protected string $view = 'filament.pages.activity-calendar';

    public static function canAccess(): bool
    {
        return Auth::user()?->can('viewAny', Event::class) === true
            || Auth::user()?->can('viewAny', Study::class) === true;
    }

    public function getTitle(): string
    {
        return 'Kalender Kegiatan';
    }
}
