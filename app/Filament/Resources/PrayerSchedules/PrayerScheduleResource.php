<?php

namespace App\Filament\Resources\PrayerSchedules;

use App\Filament\Resources\PrayerSchedules\Pages\CreatePrayerSchedule;
use App\Filament\Resources\PrayerSchedules\Pages\EditPrayerSchedule;
use App\Filament\Resources\PrayerSchedules\Pages\ListPrayerSchedules;
use App\Models\PrayerSchedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Jadwal sholat harian (PRD 5.2.14). Data diisi otomatis dari API lewat
 * `masjid:sync-prayer-schedules`; baris yang ditandai "override" dikecualikan
 * dari sinkronisasi agar penyesuaian lokal tidak tertimpa.
 */
class PrayerScheduleResource extends Resource
{
    protected static ?string $model = PrayerSchedule::class;

    protected static string|UnitEnum|null $navigationGroup = 'Profil Masjid';

    protected static ?string $navigationLabel = 'Jadwal Sholat';

    protected static ?string $modelLabel = 'jadwal sholat';

    protected static ?string $pluralModelLabel = 'jadwal sholat';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'date';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tanggal')
                ->schema([
                    DatePicker::make('date')
                        ->label('Tanggal')
                        ->native(false)
                        ->required()
                        ->unique(ignoreRecord: true),

                    Toggle::make('is_override')
                        ->label('Kunci dari sinkronisasi otomatis')
                        ->helperText('Aktifkan bila jadwal hari ini disesuaikan manual dan tidak boleh ditimpa API.')
                        ->default(true),

                    TextInput::make('note')
                        ->label('Catatan')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Waktu Sholat')
                ->schema(
                    collect(PrayerSchedule::PRAYERS)
                        ->map(fn (string $label, string $key): TimePicker => TimePicker::make($key)
                            ->label($label)
                            ->seconds(false)
                            ->required(! in_array($key, ['imsak', 'sunrise'], true)))
                        ->values()
                        ->all()
                )
                ->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        $timeColumns = collect(PrayerSchedule::PRAYERS)
            ->map(fn (string $label, string $key): TextColumn => TextColumn::make($key)
                ->label($label)
                ->time('H:i')
                ->placeholder('—'))
            ->values()
            ->all();

        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('D, d M Y')
                    ->sortable(),

                ...$timeColumns,

                IconColumn::make('is_override')
                    ->label('Override')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-arrow-path')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                TernaryFilter::make('is_override')
                    ->label('Hanya jadwal override'),

                Filter::make('upcoming')
                    ->label('Mulai hari ini')
                    ->default()
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', '>=', today())),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrayerSchedules::route('/'),
            'create' => CreatePrayerSchedule::route('/create'),
            'edit' => EditPrayerSchedule::route('/{record}/edit'),
        ];
    }
}
