<?php

namespace App\Filament\Exports;

use App\Filament\Exports\Concerns\SendsCompletedExportNotification;
use App\Models\FinanceTransaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

/**
 * Export laporan keuangan untuk bendahara (PRD 5.2.3).
 *
 * Kolom sengaja memisahkan pemasukan dan pengeluaran ke dua lajur agar berkas
 * hasilnya bisa langsung dijumlahkan di spreadsheet tanpa diolah lagi.
 */
class FinanceTransactionExporter extends Exporter
{
    use SendsCompletedExportNotification;

    protected static ?string $model = FinanceTransaction::class;

    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date')
                ->label('Tanggal')
                ->formatStateUsing(fn ($state): string => $state?->translatedFormat('d/m/Y') ?? ''),

            ExportColumn::make('category.name')
                ->label('Kategori'),

            ExportColumn::make('type')
                ->label('Jenis')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),

            ExportColumn::make('description')
                ->label('Keterangan'),

            ExportColumn::make('reference_no')
                ->label('Nomor bukti'),

            ExportColumn::make('pemasukan')
                ->label('Pemasukan')
                ->state(fn (FinanceTransaction $record): string => $record->type->value === 'in'
                    ? number_format((float) $record->amount, 0, ',', '.')
                    : ''),

            ExportColumn::make('pengeluaran')
                ->label('Pengeluaran')
                ->state(fn (FinanceTransaction $record): string => $record->type->value === 'out'
                    ? number_format((float) $record->amount, 0, ',', '.')
                    : ''),

            ExportColumn::make('creator.name')
                ->label('Diinput oleh'),
        ];
    }

    /**
     * Export dijalankan langsung, bukan lewat antrean.
     *
     * Skala data satu masjid kecil, sementara memaksakan antrean berarti berkas
     * tidak pernah jadi bila queue worker kebetulan tidak berjalan di server.
     */
    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Rekap keuangan siap diunduh';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "{$export->successful_rows} transaksi berhasil diekspor.";

        if ($gagal = $export->getFailedRowsCount()) {
            $body .= " {$gagal} baris gagal diekspor.";
        }

        return $body;
    }
}
