<?php

namespace App\Filament\Resources\QurbanRegistrations;

use App\Enums\PaymentStatus;
use App\Enums\QurbanServiceType;
use App\Filament\Resources\QurbanRegistrations\Pages\CreateQurbanRegistration;
use App\Filament\Resources\QurbanRegistrations\Pages\EditQurbanRegistration;
use App\Filament\Resources\QurbanRegistrations\Pages\ListQurbanRegistrations;
use App\Models\QurbanRegistration;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Pendaftaran kurban & aqiqah (PRD 5.2.9). Pembayaran tetap manual: bendahara
 * menandai "Lunas" setelah menerima konfirmasi transfer.
 */
class QurbanRegistrationResource extends Resource
{
    protected static ?string $model = QurbanRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Jamaah';

    protected static ?string $navigationLabel = 'Kurban & Aqiqah';

    protected static ?string $modelLabel = 'pendaftaran kurban';

    protected static ?string $pluralModelLabel = 'pendaftaran kurban';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'registration_number';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Pendaftar')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama pendaftar')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Nomor kontak')
                        ->tel()
                        ->required()
                        ->maxLength(30),

                    Select::make('service_type')
                        ->label('Jenis layanan')
                        ->options(QurbanServiceType::class)
                        ->default(QurbanServiceType::Kurban)
                        ->required(),

                    Select::make('animal_type')
                        ->label('Jenis hewan')
                        ->options(QurbanRegistration::ANIMAL_TYPES)
                        ->required(),

                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),

                    TextInput::make('amount')
                        ->label('Nominal')
                        ->numeric()
                        ->prefix('Rp')
                        ->helperText('Opsional, diisi bendahara sesuai kesepakatan panitia.'),

                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Status Pembayaran')
                ->schema([
                    Select::make('payment_status')
                        ->label('Status pembayaran')
                        ->options(PaymentStatus::class)
                        ->default(PaymentStatus::BelumBayar)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration_number')
                    ->label('No. pendaftaran')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Pendaftar')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Kontak')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('service_type')
                    ->label('Layanan')
                    ->badge(),

                TextColumn::make('animal_type')
                    ->label('Hewan')
                    ->state(fn (QurbanRegistration $record): string => $record->animalLabel()),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->summarize(Sum::make()->label('Total ekor')),

                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Status pembayaran')
                    ->options(PaymentStatus::class),

                SelectFilter::make('service_type')
                    ->label('Jenis layanan')
                    ->options(QurbanServiceType::class),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Konfirmasi bahwa pembayaran sudah diterima panitia.')
                    ->visible(fn (QurbanRegistration $record): bool => $record->payment_status === PaymentStatus::BelumBayar
                        && Auth::user()?->can('update', $record) === true)
                    ->action(function (QurbanRegistration $record): void {
                        /** @var User $confirmer */
                        $confirmer = Auth::user();

                        $record->forceFill([
                            'payment_status' => PaymentStatus::Lunas,
                            'confirmed_by' => $confirmer->getKey(),
                            'confirmed_at' => now(),
                        ])->save();
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

    public static function getPages(): array
    {
        return [
            'index' => ListQurbanRegistrations::route('/'),
            'create' => CreateQurbanRegistration::route('/create'),
            'edit' => EditQurbanRegistration::route('/{record}/edit'),
        ];
    }
}
