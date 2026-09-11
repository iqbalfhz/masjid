<?php

namespace App\Filament\Widgets;

use App\Enums\ModerationStatus;
use App\Enums\SuggestionStatus;
use App\Models\Suggestion;
use App\Models\Testimonial;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Masukan jamaah terbaru yang belum ditangani.
 *
 * Sekretaris perlu membaca isinya untuk memilah mana yang mendesak, bukan
 * sekadar tahu jumlahnya. Angka "7 perlu moderasi" tidak membedakan keluhan
 * kebocoran atap dari pujian biasa, padahal keduanya menuntut kecepatan yang
 * jauh berbeda.
 *
 * Sama seperti antrean approval, dua sumber berbeda disatukan sebagai record
 * array agar kuncinya tidak bertabrakan.
 */
class JamaahInputWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null
            && ($user->can('moderate:testimonial') || $user->can('update:suggestion'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Masukan Jamaah Terbaru')
            ->description('Testimoni dan kotak saran yang belum ditangani.')
            ->records(fn (): Collection => $this->rows())
            ->columns([
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (array $record): string => $record['jenis'] === 'Testimoni' ? 'info' : 'warning'),

                TextColumn::make('nama')
                    ->label('Dari'),

                TextColumn::make('isi')
                    ->label('Isi')
                    ->wrap(),

                TextColumn::make('masuk')
                    ->label('Masuk')
                    ->since()
                    ->dateTimeTooltip('d F Y, H:i'),
            ])
            ->recordActions([
                Action::make('tangani')
                    ->label('Tangani')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn (array $record): string => $record['url']),
            ])
            ->emptyStateHeading('Tidak ada masukan baru')
            ->emptyStateDescription('Semua testimoni dan kotak saran sudah ditangani.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10]);
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    private function rows(): Collection
    {
        $user = Auth::user();
        $rows = new Collection;

        if ($user?->can('moderate:testimonial')) {
            foreach (Testimonial::query()->where('status', ModerationStatus::Menunggu)->latest()->limit(10)->get() as $item) {
                $rows->put('testimoni:'.$item->getKey(), [
                    'key' => 'testimoni:'.$item->getKey(),
                    'jenis' => 'Testimoni',
                    'nama' => $item->name,
                    'isi' => Str::limit((string) $item->message, 120),
                    'masuk' => $item->created_at,
                    'url' => route('filament.admin.resources.testimonials.index'),
                ]);
            }
        }

        if ($user?->can('update:suggestion')) {
            foreach (Suggestion::query()->where('status', SuggestionStatus::Baru)->latest()->limit(10)->get() as $item) {
                $rows->put('saran:'.$item->getKey(), [
                    'key' => 'saran:'.$item->getKey(),
                    'jenis' => 'Kotak Saran',
                    'nama' => $item->name ?: 'Anonim',
                    'isi' => Str::limit((string) $item->message, 120),
                    'masuk' => $item->created_at,
                    'url' => route('filament.admin.resources.suggestions.index'),
                ]);
            }
        }

        return $rows->sortByDesc(fn (array $row): mixed => $row['masuk']);
    }
}
