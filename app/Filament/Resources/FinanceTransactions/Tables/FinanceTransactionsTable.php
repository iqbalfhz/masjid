<?php

namespace App\Filament\Resources\FinanceTransactions\Tables;

use App\Enums\TransactionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinanceTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money('IDR')),

                TextColumn::make('reference_no')
                    ->label('No. bukti')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Diinput oleh')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(TransactionType::class),

                SelectFilter::make('finance_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->preload()
                    ->multiple(),

                Filter::make('date_range')
                    ->label('Rentang tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari')->native(false),
                        DatePicker::make('until')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, string $date) => $q->whereDate('date', '<=', $date))),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Menghapus transaksi akan mengubah ringkasan bulanan yang tampil publik. Pastikan ini memang koreksi yang disengaja.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
