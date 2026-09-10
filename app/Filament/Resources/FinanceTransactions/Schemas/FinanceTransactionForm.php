<?php

namespace App\Filament\Resources\FinanceTransactions\Schemas;

use App\Enums\TransactionType;
use App\Models\FinanceCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class FinanceTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Transaksi')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->native(false)
                            ->default(today())
                            ->maxDate(today())
                            ->required(),

                        Select::make('type')
                            ->label('Jenis')
                            ->options(TransactionType::class)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('finance_category_id', null)),

                        Select::make('finance_category_id')
                            ->label('Kategori')
                            ->options(fn (Get $get): array => FinanceCategory::query()
                                ->where('is_active', true)
                                ->when($get('type'), fn ($query, $type) => $query->where('type', $type))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->helperText('Pilih jenis transaksi lebih dulu agar daftar kategori menyesuaikan.'),

                        TextInput::make('amount')
                            ->label('Nominal')
                            ->numeric()
                            ->minValue(1)
                            ->prefix('Rp')
                            ->required(),

                        TextInput::make('reference_no')
                            ->label('Nomor bukti')
                            ->maxLength(255)
                            ->helperText('Opsional: nomor kuitansi atau referensi transfer.'),

                        // Disandingkan dengan nomor bukti agar baris terakhir tidak menyisakan sel kosong.
                        Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
