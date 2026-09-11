<?php

namespace App\Filament\Resources\Studies\Tables;

use App\Enums\ContentStatus;
use App\Enums\ScheduleType;
use App\Filament\Support\ApprovalActions;
use App\Models\Study;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('theme')
                    ->label('Tema')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                TextColumn::make('ustadz_name')
                    ->label('Pemateri')
                    ->searchable(),

                TextColumn::make('schedule')
                    ->label('Jadwal')
                    ->state(fn (Study $record): string => $record->scheduleLabel()),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('rsvps_count')
                    ->label('RSVP')
                    ->counts('rsvps')
                    ->badge()
                    ->color('info'),

                TextColumn::make('creator.name')
                    ->label('Dibuat oleh')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviewer.name')
                    ->label('Ditinjau oleh')
                    ->placeholder('Belum ditinjau')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ContentStatus::class),

                SelectFilter::make('schedule_type')
                    ->label('Jenis jadwal')
                    ->options(ScheduleType::class),
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
