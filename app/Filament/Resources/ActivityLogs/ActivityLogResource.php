<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\KeyValueEntry;
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
 */
class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Log Aktivitas';

    protected static ?string $modelLabel = 'log aktivitas';

    protected static ?string $pluralModelLabel = 'log aktivitas';

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
        return $schema->components([
            Section::make('Ringkasan')
                ->schema([
                    TextEntry::make('created_at')->label('Waktu')->dateTime('d F Y, H:i:s'),
                    TextEntry::make('causer.name')->label('Pelaku')->placeholder('Sistem'),
                    TextEntry::make('log_name')->label('Modul')->badge(),
                    TextEntry::make('event')->label('Aksi')->badge(),
                    TextEntry::make('description')->label('Keterangan')->columnSpanFull(),
                    TextEntry::make('subject_type')->label('Record')->formatStateUsing(
                        fn (?string $state, Activity $record): string => $state === null
                            ? '—'
                            : class_basename($state).' #'.$record->subject_id
                    ),
                ])
                ->columns(2),

            Section::make('Perubahan Data')
                ->schema([
                    KeyValueEntry::make('attribute_changes.old')
                        ->label('Sebelum')
                        ->keyLabel('Kolom')
                        ->valueLabel('Nilai lama'),

                    KeyValueEntry::make('attribute_changes.attributes')
                        ->label('Sesudah')
                        ->keyLabel('Kolom')
                        ->valueLabel('Nilai baru'),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('Pelaku')
                    ->placeholder('Sistem')
                    ->searchable(),

                TextColumn::make('log_name')
                    ->label('Modul')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'submitted' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(70),

                TextColumn::make('subject_type')
                    ->label('Record')
                    ->formatStateUsing(fn (?string $state, Activity $record): string => $state === null
                        ? '—'
                        : class_basename($state).' #'.$record->subject_id)
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Modul')
                    ->options(fn (): array => Activity::query()
                        ->whereNotNull('log_name')
                        ->distinct()
                        ->pluck('log_name', 'log_name')
                        ->all()),

                SelectFilter::make('event')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Membuat',
                        'updated' => 'Mengubah',
                        'deleted' => 'Menghapus',
                        'submitted' => 'Mengajukan approval',
                        'approved' => 'Menyetujui',
                        'rejected' => 'Menolak',
                        'login' => 'Login',
                    ]),

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
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
