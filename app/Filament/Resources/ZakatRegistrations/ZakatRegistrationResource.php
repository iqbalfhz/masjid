<?php

namespace App\Filament\Resources\ZakatRegistrations;

use App\Enums\PaymentStatus;
use App\Enums\ZakatType;
use App\Filament\Exports\ZakatRegistrationExporter;
use App\Filament\Resources\ZakatRegistrations\Pages\CreateZakatRegistration;
use App\Filament\Resources\ZakatRegistrations\Pages\EditZakatRegistration;
use App\Filament\Resources\ZakatRegistrations\Pages\ListZakatRegistrations;
use App\Models\User;
use App\Models\ZakatRegistration;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * Pendaftaran zakat fitrah & maal (PRD 5.2.10), pembayaran manual seperti kurban.
 */
class ZakatRegistrationResource extends Resource
{
    protected static ?string $model = ZakatRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Jamaah';

    protected static ?string $navigationLabel = 'Zakat';

    protected static ?string $modelLabel = 'pendaftaran zakat';

    protected static ?string $pluralModelLabel = 'pendaftaran zakat';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'registration_number';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Muzakki')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama muzakki')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('phone')
                        ->label('Nomor kontak')
                        ->tel()
                        ->required()
                        ->maxLength(30),

                    Select::make('zakat_type')
                        ->label('Jenis zakat')
                        ->options(ZakatType::class)
                        ->default(ZakatType::Fitrah)
                        ->required()
                        ->live(),

                    TextInput::make('soul_count')
                        ->label('Jumlah jiwa')
                        ->numeric()
                        ->minValue(1)
                        ->required(fn (Get $get): bool => $get('zakat_type') === ZakatType::Fitrah->value)
                        ->visible(fn (Get $get): bool => $get('zakat_type') === ZakatType::Fitrah->value),

                    TextInput::make('amount')
                        ->label('Nominal')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(fn (Get $get): bool => $get('zakat_type') === ZakatType::Maal->value)
                        ->columnSpan(fn (Get $get): int => $get('zakat_type') === ZakatType::Fitrah->value ? 2 : 1),

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
                        ->required()
                        ->columnSpanFull(),
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
                    ->label('Muzakki')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Kontak')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('zakat_type')
                    ->label('Jenis')
                    ->badge(),

                TextColumn::make('soul_count')
                    ->label('Jiwa')
                    ->placeholder('—')
                    ->summarize(Sum::make()->label('Total jiwa')),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->placeholder('—')
                    ->summarize(Sum::make()->label('Total')->money('IDR')),

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

                SelectFilter::make('zakat_type')
                    ->label('Jenis zakat')
                    ->options(ZakatType::class),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ZakatRegistration $record): bool => $record->payment_status === PaymentStatus::BelumBayar
                        && Auth::user()?->can('update', $record) === true)
                    ->action(function (ZakatRegistration $record): void {
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
            ->headerActions([
                ExportAction::make()
                    ->label('Export rekap zakat')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exporter(ZakatRegistrationExporter::class)
                    ->columnMapping(false)
                    ->enableVisibleTableColumnsByDefault(false),
            ])
            ->toolbarActions([
                ExportBulkAction::make()
                    ->label('Export yang dipilih')
                    ->exporter(ZakatRegistrationExporter::class)
                    ->columnMapping(false)
                    ->enableVisibleTableColumnsByDefault(false),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListZakatRegistrations::route('/'),
            'create' => CreateZakatRegistration::route('/create'),
            'edit' => EditZakatRegistration::route('/{record}/edit'),
        ];
    }
}
