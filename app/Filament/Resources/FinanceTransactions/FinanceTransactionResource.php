<?php

namespace App\Filament\Resources\FinanceTransactions;

use App\Filament\Resources\FinanceTransactions\Pages\CreateFinanceTransaction;
use App\Filament\Resources\FinanceTransactions\Pages\EditFinanceTransaction;
use App\Filament\Resources\FinanceTransactions\Pages\ListFinanceTransactions;
use App\Filament\Resources\FinanceTransactions\Schemas\FinanceTransactionForm;
use App\Filament\Resources\FinanceTransactions\Tables\FinanceTransactionsTable;
use App\Models\FinanceTransaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FinanceTransactionResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'transaksi';

    protected static ?string $pluralModelLabel = 'transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return FinanceTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinanceTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinanceTransactions::route('/'),
            'create' => CreateFinanceTransaction::route('/create'),
            'edit' => EditFinanceTransaction::route('/{record}/edit'),
        ];
    }
}
