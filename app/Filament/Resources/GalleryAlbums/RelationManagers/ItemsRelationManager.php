<?php

namespace App\Filament\Resources\GalleryAlbums\RelationManagers;

use App\Enums\GalleryItemType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Isi album: foto yang diunggah atau video yang ditautkan (PRD 5.2.4).
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Isi Album';

    protected static ?string $modelLabel = 'item galeri';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Jenis')
                    ->options(GalleryItemType::class)
                    ->default(GalleryItemType::Image)
                    ->required()
                    ->live(),

                TextInput::make('caption')
                    ->label('Keterangan')
                    ->maxLength(255),

                FileUpload::make('file_path')
                    ->label('Berkas foto')
                    ->image()
                    ->directory('galeri')
                    ->imageEditor()
                    ->maxSize(8192)
                    ->required(fn (Get $get): bool => $get('type') === GalleryItemType::Image->value)
                    ->visible(fn (Get $get): bool => $get('type') === GalleryItemType::Image->value)
                    ->columnSpanFull(),

                TextInput::make('external_url')
                    ->label('Tautan video')
                    ->url()
                    ->helperText('Tempel tautan YouTube kegiatan.')
                    ->required(fn (Get $get): bool => $get('type') === GalleryItemType::Video->value)
                    ->visible(fn (Get $get): bool => $get('type') === GalleryItemType::Video->value)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Urutan tampil')
                    ->numeric()
                    ->default(0),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption')
            ->columns([
                ImageColumn::make('file_path')
                    ->label('Pratinjau')
                    ->height(50)
                    ->defaultImageUrl(asset('images/placeholder.svg')),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge(),

                TextColumn::make('caption')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()->label('Tambah item'),

                Action::make('bulkUpload')
                    ->label('Unggah banyak foto')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        FileUpload::make('files')
                            ->label('Pilih foto')
                            ->image()
                            ->multiple()
                            ->directory('galeri')
                            ->maxSize(8192)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $album = $this->getOwnerRecord();
                        $order = (int) $album->items()->max('sort_order');

                        foreach ($data['files'] as $path) {
                            $album->items()->create([
                                'type' => GalleryItemType::Image,
                                'file_path' => $path,
                                'sort_order' => ++$order,
                            ]);
                        }

                        Notification::make()
                            ->title(count($data['files']).' foto berhasil ditambahkan')
                            ->success()
                            ->send();
                    }),
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
}
