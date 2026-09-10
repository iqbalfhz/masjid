<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\User;
use App\Support\ActivityLogPresenter;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/**
 * Log Aktivitas / audit trail (PRD 5.2.16).
 *
 * Read-only untuk semua role, termasuk Superadmin: catatan tidak boleh
 * diubah atau dihapus lewat panel agar tetap bisa dipertanggungjawabkan.
 *
 * Seluruh nama modul, aksi, kolom, dan nilainya diterjemahkan lewat
 * ActivityLogPresenter supaya terbaca oleh pengurus yang bukan teknis.
 */
class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'catatan aktivitas';

    protected static ?string $pluralModelLabel = 'catatan aktivitas';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return false;
    }

    public static function canDelete(mixed $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        $presenter = app(ActivityLogPresenter::class);

        return $schema->components([
            Section::make('Ringkasan')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Waktu')
                        ->dateTime('l, d F Y, H:i')
                        ->suffix(' WIB'),

                    TextEntry::make('causer')
                        ->label('Dilakukan oleh')
                        ->state(fn (Activity $record): string => $presenter->causerName($record)),

                    TextEntry::make('log_name')
                        ->label('Modul')
                        ->badge()
                        ->state(fn (Activity $record): string => $presenter->moduleLabel($record)),

                    TextEntry::make('event')
                        ->label('Aksi')
                        ->badge()
                        ->state(fn (Activity $record): string => $presenter->eventLabel($record->event))
                        ->color(fn (Activity $record): string => $presenter->eventColor($record->event)),

                    TextEntry::make('subject')
                        ->label('Data yang disentuh')
                        ->state(fn (Activity $record): string => $presenter->recordLabel($record))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make(fn (Activity $record): string => $presenter->changesHeading($record))
                ->description(fn (Activity $record): ?string => $presenter->isComparison($record)
                    ? 'Perbandingan nilai sebelum dan sesudah perubahan.'
                    : 'Nilai yang tercatat saat aksi ini dilakukan.')
                ->schema([
                    RepeatableEntry::make('perubahan')
                        ->hiddenLabel()
                        ->state(fn (Activity $record): array => $presenter->changes($record))
                        ->schema([
                            TextEntry::make('kolom')
                                ->label('Yang diubah')
                                ->weight('medium'),

                            // Pada data baru atau terhapus kolom ini berisi "—",
                            // menandakan memang tidak ada nilai pembanding.
                            TextEntry::make('sebelum')
                                ->label('Sebelumnya')
                                ->color('gray'),

                            TextEntry::make('sesudah')
                                ->label('Nilai tercatat')
                                ->color('success'),
                        ])
                        ->columns(3),
                ])
                ->visible(fn (Activity $record): bool => $presenter->changes($record) !== []),
        ]);
    }

    public static function table(Table $table): Table
    {
        $presenter = app(ActivityLogPresenter::class);

        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn (Activity $record): string => $record->created_at->diffForHumans()),

                TextColumn::make('causer.name')
                    ->label('Pelaku')
                    ->state(fn (Activity $record): string => $presenter->causerName($record))
                    ->searchable(),

                TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->state(fn (Activity $record): string => $presenter->eventLabel($record->event))
                    ->color(fn (Activity $record): string => $presenter->eventColor($record->event)),

                TextColumn::make('log_name')
                    ->label('Modul')
                    ->badge()
                    ->color('gray')
                    ->state(fn (Activity $record): string => $presenter->moduleLabel($record)),

                TextColumn::make('subject_id')
                    ->label('Data yang disentuh')
                    ->state(fn (Activity $record): string => $presenter->recordLabel($record))
                    ->wrap(),

                TextColumn::make('description')
                    ->label('Ringkasan perubahan')
                    ->state(fn (Activity $record): string => $presenter->changeSummary($record))
                    ->color('gray')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Modul')
                    ->options(fn (): array => Activity::query()
                        ->whereNotNull('log_name')
                        ->distinct()
                        ->pluck('log_name')
                        ->mapWithKeys(fn (string $name): array => [
                            $name => ActivityLogPresenter::MODULES[str_replace(' ', '_', $name)] ?? $name,
                        ])
                        ->sort()
                        ->all()),

                SelectFilter::make('event')
                    ->label('Aksi')
                    ->options(collect(ActivityLogPresenter::EVENTS)
                        ->map(fn (array $event): string => $event['label'])
                        ->all()),

                SelectFilter::make('causer_id')
                    ->label('Pelaku')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),

                Filter::make('date_range')
                    ->label('Rentang tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari')->native(false),
                        DatePicker::make('until')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()->label('Rincian'),
            ]);
    }

    /**
     * Pelaku dan record dimuat sekaligus agar tabel tidak menembak query
     * tambahan per baris.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['causer', 'subject']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
