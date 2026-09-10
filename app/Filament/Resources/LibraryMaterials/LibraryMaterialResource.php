<?php

namespace App\Filament\Resources\LibraryMaterials;

use App\Enums\MaterialType;
use App\Filament\Resources\LibraryMaterials\Pages\CreateLibraryMaterial;
use App\Filament\Resources\LibraryMaterials\Pages\EditLibraryMaterial;
use App\Filament\Resources\LibraryMaterials\Pages\ListLibraryMaterials;
use App\Models\LibraryMaterial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Arsip materi kajian: slide, dokumen, rekaman audio/video (PRD 5.2.6).
 */
class LibraryMaterialResource extends Resource
{
    protected static ?string $model = LibraryMaterial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Konten & Informasi';

    protected static ?string $navigationLabel = 'E-Library';

    protected static ?string $modelLabel = 'materi kajian';

    protected static ?string $pluralModelLabel = 'materi kajian';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Materi')
                ->schema([
                    TextInput::make('title')
                        ->label('Judul materi')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Select::make('type')
                        ->label('Jenis materi')
                        ->options(MaterialType::class)
                        ->default(MaterialType::Pdf)
                        ->required()
                        ->live(),

                    Select::make('study_id')
                        ->label('Kajian terkait')
                        ->relationship('study', 'theme')
                        ->searchable()
                        ->preload()
                        ->helperText('Kosongkan bila materi tidak terikat kajian rutin.'),

                    TextInput::make('ustadz_name')
                        ->label('Nama ustadz/pemateri')
                        ->maxLength(255),

                    DatePicker::make('material_date')
                        ->label('Tanggal kajian')
                        ->native(false),

                    FileUpload::make('file_path')
                        ->label('Berkas materi')
                        ->directory('e-library')
                        ->acceptedFileTypes(['application/pdf', 'audio/mpeg', 'audio/mp4', 'video/mp4', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])
                        ->maxSize(51200)
                        ->helperText('Unggah berkas, atau kosongkan dan isi tautan eksternal di bawah.')
                        ->columnSpanFull(),

                    TextInput::make('external_url')
                        ->label('Tautan eksternal')
                        ->url()
                        ->helperText('Untuk rekaman YouTube atau berkas di Google Drive.')
                        ->required(fn (Get $get): bool => blank($get('file_path')))
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Ringkasan materi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge(),

                TextColumn::make('study.theme')
                    ->label('Kajian')
                    ->placeholder('—')
                    ->limit(30),

                TextColumn::make('ustadz_name')
                    ->label('Pemateri')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('material_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('downloads')
                    ->label('Diakses')
                    ->numeric()
                    ->toggleable(),
            ])
            ->defaultSort('material_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis materi')
                    ->options(MaterialType::class),

                SelectFilter::make('study_id')
                    ->label('Kajian')
                    ->relationship('study', 'theme')
                    ->preload(),
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
            'index' => ListLibraryMaterials::route('/'),
            'create' => CreateLibraryMaterial::route('/create'),
            'edit' => EditLibraryMaterial::route('/{record}/edit'),
        ];
    }
}
