<?php

namespace App\Filament\Resources\Studies\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Rekap konfirmasi kehadiran jamaah per kajian (PRD 5.2.2).
 */
class RsvpsRelationManager extends RelationManager
{
    protected static string $relationship = 'rsvps';

    protected static ?string $title = 'Rekap RSVP';

    protected static ?string $modelLabel = 'konfirmasi kehadiran';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama jamaah')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Nomor kontak')
                    ->tel()
                    ->required()
                    ->maxLength(30),

                TextInput::make('person_count')
                    ->label('Jumlah orang')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),

                Textarea::make('note')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Kontak')
                    ->searchable(),

                TextColumn::make('person_count')
                    ->label('Jumlah orang')
                    ->summarize(Sum::make()->label('Total peserta')),

                TextColumn::make('note')
                    ->label('Catatan')
                    ->placeholder('—')
                    ->limit(40),

                TextColumn::make('created_at')
                    ->label('Waktu daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()->label('Tambah manual'),
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
