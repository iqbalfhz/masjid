<?php

namespace App\Filament\Support;

use App\Enums\ContentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Blok form "Status & Riwayat Approval" yang dipakai seluruh modul berpola
 * approval: Pengumuman, Kajian, Kegiatan, dan Artikel (PRD 5.2).
 */
class ApprovalSchema
{
    /**
     * @param  class-string<Model>  $modelClass  model resource terkait, dipakai untuk cek policy approve
     */
    public static function section(string $modelClass): Section
    {
        return Section::make('Status & Riwayat Approval')
            ->description('Riwayat siapa yang membuat dan siapa yang mengambil keputusan atas konten ini.')
            ->schema([
                Select::make('status')
                    ->label('Status')
                    ->options(ContentStatus::class)
                    ->default(ContentStatus::Draft)
                    ->required()
                    ->disabled(fn (): bool => Auth::user()?->can('approve', $modelClass) !== true)
                    ->dehydrated()
                    ->helperText('Sekretaris cukup menyimpan sebagai Draft lalu tekan "Ajukan Approval". Status hanya bisa diubah langsung oleh approver.'),

                Textarea::make('approval_note')
                    ->label('Catatan reviewer')
                    ->rows(2)
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (?Model $record): bool => filled($record?->approval_note)),

                TextEntry::make('creator.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—'),

                TextEntry::make('reviewer.name')
                    ->label('Ditinjau oleh')
                    ->placeholder('Belum ditinjau'),

                TextEntry::make('reviewed_at')
                    ->label('Waktu peninjauan')
                    ->dateTime('d F Y, H:i')
                    ->suffix(' WIB')
                    ->placeholder('—'),
            ])
            ->columns(2);
    }
}
