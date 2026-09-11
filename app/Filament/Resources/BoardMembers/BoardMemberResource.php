<?php

namespace App\Filament\Resources\BoardMembers;

use App\Filament\Resources\BoardMembers\Pages\CreateBoardMember;
use App\Filament\Resources\BoardMembers\Pages\EditBoardMember;
use App\Filament\Resources\BoardMembers\Pages\ListBoardMembers;
use App\Models\BoardMember;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BoardMemberResource extends Resource
{
    protected static ?string $model = BoardMember::class;

    protected static string|UnitEnum|null $navigationGroup = 'Profil Masjid';

    protected static ?string $navigationLabel = 'Pengurus DKM';

    protected static ?string $modelLabel = 'pengurus';

    protected static ?string $pluralModelLabel = 'pengurus';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama lengkap')
                ->required()
                ->maxLength(255),

            TextInput::make('position')
                ->label('Jabatan')
                ->required()
                ->maxLength(255),

            TextInput::make('period_start')
                ->label('Periode mulai')
                ->numeric()
                ->minValue(2000)
                ->maxValue(2100)
                ->default(now()->year)
                ->required(),

            TextInput::make('period_end')
                ->label('Periode selesai')
                ->numeric()
                ->minValue(2000)
                ->maxValue(2100)
                ->helperText('Kosongkan bila masih menjabat.'),

            FileUpload::make('photo')
                ->label('Foto')
                ->image()
                ->avatar()
                ->directory('pengurus')
                ->imageEditor()
                ->maxSize(2048),

            Textarea::make('bio')
                ->label('Keterangan singkat')
                ->rows(3),

            TextInput::make('sort_order')
                ->label('Urutan tampil')
                ->numeric()
                ->default(0),

            Toggle::make('is_active')
                ->label('Pengurus aktif')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('images/placeholder.svg')),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),

                TextColumn::make('period')
                    ->label('Periode')
                    ->state(fn (BoardMember $record): string => $record->periodLabel()),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => ListBoardMembers::route('/'),
            'create' => CreateBoardMember::route('/create'),
            'edit' => EditBoardMember::route('/{record}/edit'),
        ];
    }
}
