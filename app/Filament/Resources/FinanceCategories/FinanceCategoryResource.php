<?php

namespace App\Filament\Resources\FinanceCategories;

use App\Enums\TransactionType;
use App\Filament\Resources\FinanceCategories\Pages\CreateFinanceCategory;
use App\Filament\Resources\FinanceCategories\Pages\EditFinanceCategory;
use App\Filament\Resources\FinanceCategories\Pages\ListFinanceCategories;
use App\Models\FinanceCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Kategori keuangan sengaja dibuat sebagai master data (PRD 5.2.3) supaya
 * bendahara bisa menambah kategori baru tanpa mengubah kode.
 */
class FinanceCategoryResource extends Resource
{
    protected static ?string $model = FinanceCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Kategori Keuangan';

    protected static ?string $modelLabel = 'kategori keuangan';

    protected static ?string $pluralModelLabel = 'kategori keuangan';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama kategori')
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label('Jenis')
                ->options(TransactionType::class)
                ->required(),

            Textarea::make('description')
                ->label('Keterangan')
                ->rows(2)
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true)
                ->helperText('Kategori nonaktif tidak muncul saat input transaksi baru.')
                ->columnSpanFull(),
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

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge(),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->limit(50),

                TextColumn::make('transactions_count')
                    ->label('Transaksi')
                    ->counts('transactions')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(TransactionType::class),
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
            'index' => ListFinanceCategories::route('/'),
            'create' => CreateFinanceCategory::route('/create'),
            'edit' => EditFinanceCategory::route('/{record}/edit'),
        ];
    }
}
