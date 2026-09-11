<?php

namespace App\Filament\Resources\GalleryAlbums;

use App\Filament\Resources\GalleryAlbums\Pages\CreateGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\EditGalleryAlbum;
use App\Filament\Resources\GalleryAlbums\Pages\ListGalleryAlbums;
use App\Filament\Resources\GalleryAlbums\RelationManagers\ItemsRelationManager;
use App\Models\GalleryAlbum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class GalleryAlbumResource extends Resource
{
    protected static ?string $model = GalleryAlbum::class;

    protected static string|UnitEnum|null $navigationGroup = 'Media & Pustaka';

    protected static ?string $navigationLabel = 'Galeri';

    protected static ?string $modelLabel = 'album galeri';

    protected static ?string $pluralModelLabel = 'album galeri';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Album')
                ->schema([
                    TextInput::make('title')
                        ->label('Judul album')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('category')
                        ->label('Kategori kegiatan')
                        ->datalist(['Kajian', 'Ramadhan', 'Sosial', 'Hari Besar', 'Renovasi'])
                        ->maxLength(255),

                    DatePicker::make('event_date')
                        ->label('Tanggal kegiatan')
                        ->native(false),

                    Toggle::make('is_published')
                        ->label('Tampilkan di website')
                        ->default(true),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),

                    FileUpload::make('cover_image')
                        ->label('Sampul album')
                        ->image()
                        ->directory('galeri/sampul')
                        ->imageEditor()
                        ->maxSize(4096),

                    TagsInput::make('tags')
                        ->label('Tag'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Sampul')
                    ->height(40)
                    ->defaultImageUrl(asset('images/placeholder.svg')),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Isi album')
                    ->counts('items')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_published')
                    ->label('Tayang')
                    ->boolean(),
            ])
            ->defaultSort('event_date', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(fn (): array => GalleryAlbum::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category', 'category')
                        ->all()),
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

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryAlbums::route('/'),
            'create' => CreateGalleryAlbum::route('/create'),
            'edit' => EditGalleryAlbum::route('/{record}/edit'),
        ];
    }
}
