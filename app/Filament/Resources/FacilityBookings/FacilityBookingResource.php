<?php

namespace App\Filament\Resources\FacilityBookings;

use App\Enums\BookingStatus;
use App\Filament\Resources\FacilityBookings\Pages\CreateFacilityBooking;
use App\Filament\Resources\FacilityBookings\Pages\EditFacilityBooking;
use App\Filament\Resources\FacilityBookings\Pages\ListFacilityBookings;
use App\Models\FacilityBooking;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Pengajuan peminjaman fasilitas (PRD 5.2.11), termasuk deteksi bentrok jadwal
 * dan alur setujui/tolak oleh Ketua DKM.
 */
class FacilityBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Jamaah';

    protected static ?string $navigationLabel = 'Peminjaman Fasilitas';

    protected static ?string $modelLabel = 'pengajuan peminjaman';

    protected static ?string $pluralModelLabel = 'pengajuan peminjaman';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'booking_number';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Pengajuan')
                ->schema([
                    Select::make('facility_id')
                        ->label('Fasilitas')
                        ->relationship('facility', 'name', fn ($query) => $query->where('is_active', true))
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('purpose')
                        ->label('Keperluan')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('name')
                        ->label('Nama pemohon')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Nomor kontak')
                        ->tel()
                        ->required()
                        ->maxLength(30),

                    DatePicker::make('booking_date')
                        ->label('Tanggal pemakaian')
                        ->native(false)
                        ->required(),

                    TimePicker::make('start_time')
                        ->label('Jam mulai')
                        ->seconds(false)
                        ->required(),

                    TimePicker::make('end_time')
                        ->label('Jam selesai')
                        ->seconds(false)
                        ->after('start_time')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Keputusan')
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(BookingStatus::class)
                        ->default(BookingStatus::Menunggu)
                        ->required(),

                    Textarea::make('approval_note')
                        ->label('Catatan')
                        ->rows(2)
                        ->columnSpanFull(),

                    TextEntry::make('reviewer.name')
                        ->label('Ditinjau oleh')
                        ->placeholder('Belum ditinjau'),

                    TextEntry::make('reviewed_at')
                        ->label('Waktu peninjauan')
                        ->dateTime('d F Y, H:i')
                        ->placeholder('—'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->label('No. pengajuan')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('facility.name')
                    ->label('Fasilitas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Pemohon')
                    ->searchable(),

                TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(30),

                TextColumn::make('booking_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('time_range')
                    ->label('Jam')
                    ->state(fn (FacilityBooking $record): string => substr((string) $record->start_time, 0, 5).' – '.substr((string) $record->end_time, 0, 5)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('reviewer.name')
                    ->label('Ditinjau oleh')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('booking_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(BookingStatus::class),

                SelectFilter::make('facility_id')
                    ->label('Fasilitas')
                    ->relationship('facility', 'name')
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Slot jadwal akan dikunci untuk pemohon ini.')
                    ->visible(fn (FacilityBooking $record): bool => $record->status === BookingStatus::Menunggu
                        && Auth::user()?->can('approve', $record) === true)
                    ->action(function (FacilityBooking $record): void {
                        if (FacilityBooking::hasConflict(
                            $record->facility_id,
                            $record->booking_date->toDateString(),
                            (string) $record->start_time,
                            (string) $record->end_time,
                            $record->getKey(),
                        )) {
                            Notification::make()
                                ->title('Jadwal bentrok')
                                ->body('Sudah ada pengajuan lain pada fasilitas, tanggal, dan jam yang sama.')
                                ->danger()
                                ->send();

                            return;
                        }

                        /** @var User $reviewer */
                        $reviewer = Auth::user();
                        $record->approveBy($reviewer);

                        activity('peminjaman fasilitas')
                            ->performedOn($record)
                            ->causedBy($reviewer)
                            ->event('approved')
                            ->log('Menyetujui peminjaman fasilitas');

                        Notification::make()->title('Pengajuan disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('approval_note')
                            ->label('Alasan penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (FacilityBooking $record): bool => $record->status === BookingStatus::Menunggu
                        && Auth::user()?->can('approve', $record) === true)
                    ->action(function (FacilityBooking $record, array $data): void {
                        /** @var User $reviewer */
                        $reviewer = Auth::user();
                        $record->rejectBy($reviewer, $data['approval_note']);

                        activity('peminjaman fasilitas')
                            ->performedOn($record)
                            ->causedBy($reviewer)
                            ->event('rejected')
                            ->log('Menolak peminjaman fasilitas');

                        Notification::make()->title('Pengajuan ditolak')->warning()->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::query()->where('status', BookingStatus::Menunggu)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilityBookings::route('/'),
            'create' => CreateFacilityBooking::route('/create'),
            'edit' => EditFacilityBooking::route('/{record}/edit'),
        ];
    }
}
