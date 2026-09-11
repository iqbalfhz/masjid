<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ContentStatus;
use App\Filament\Support\ApprovalActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
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
                    ->wrap()
                    ->limit(60),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('publish_date')
                    ->label('Tayang')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('views')
                    ->label('Dibaca')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('creator.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviewer.name')
                    ->label('Ditinjau oleh')
                    ->placeholder('Belum ditinjau')
                    ->toggleable(),
            ])
            ->defaultSort('publish_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ContentStatus::class),

                SelectFilter::make('article_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->preload(),
            ])
            ->recordActions([
                ...ApprovalActions::make(),
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
