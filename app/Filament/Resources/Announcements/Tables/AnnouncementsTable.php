<?php

namespace App\Filament\Resources\Announcements\Tables;

use App\Enums\AnnouncementPriority;
use App\Enums\ContentStatus;
use App\Filament\Support\ApprovalActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->wrap()
                    ->limit(60),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge(),

                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->placeholder('Tanpa batas')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviewer.name')
                    ->label('Ditinjau oleh')
                    ->placeholder('Belum ditinjau')
                    ->toggleable(),

                TextColumn::make('reviewed_at')
                    ->label('Waktu tinjau')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ContentStatus::class),

                SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options(AnnouncementPriority::class),
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
