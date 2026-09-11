<?php

namespace App\Filament\Resources\Facilities;

use App\Filament\Resources\Facilities\Pages\CreateFacility;
use App\Filament\Resources\Facilities\Pages\EditFacility;
use App\Filament\Resources\Facilities\Pages\ListFacilities;
use App\Models\Facility;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static string|UnitEnum|null $navigationGroup = 'Layanan Jamaah';

    protected static ?string $navigationLabel = 'Daftar Fasilitas';

    protected static ?string $modelLabel = 'fasilitas';

    protected static ?string $pluralModelLabel = 'fasilitas';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama fasilitas')
                ->required()
                ->maxLength(255),

            TextInput::make('capacity')
                ->label('Kapasitas (orang)')
                ->numeric()
                ->minValue(1),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->columnSpanFull(),

            FileUpload::make('image')
                ->label('Foto fasilitas')
                ->image()
                ->directory('fasilitas')
                ->imageEditor()
                ->maxSize(4096),

            Toggle::make('is_active')
                ->label('Bisa dipinjam')
                ->default(true)
                ->helperText('Nonaktifkan bila fasilitas sedang tidak tersedia untuk peminjaman.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->placeholder('—')
                    ->suffix(' orang'),

                TextColumn::make('bookings_count')
                    ->label('Pengajuan')
                    ->counts('bookings')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('name')
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
            'index' => ListFacilities::route('/'),
            'create' => CreateFacility::route('/create'),
            'edit' => EditFacility::route('/{record}/edit'),
        ];
    }
}
