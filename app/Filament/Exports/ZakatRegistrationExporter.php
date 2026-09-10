<?php

namespace App\Filament\Exports;

use App\Filament\Exports\Concerns\SendsCompletedExportNotification;
use App\Models\ZakatRegistration;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

/**
 * Rekap pendaftaran zakat untuk panitia (PRD 5.2.10).
 */
class ZakatRegistrationExporter extends Exporter
{
    use SendsCompletedExportNotification;

    protected static ?string $model = ZakatRegistration::class;

    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('registration_number')
                ->label('Nomor pendaftaran'),

            ExportColumn::make('name')
                ->label('Nama muzakki'),

            ExportColumn::make('phone')
                ->label('Nomor kontak'),

            ExportColumn::make('zakat_type')
                ->label('Jenis zakat')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),

            ExportColumn::make('soul_count')
                ->label('Jumlah jiwa'),

            ExportColumn::make('amount')
                ->label('Nominal')
                ->formatStateUsing(fn ($state): string => $state === null
                    ? ''
                    : number_format((float) $state, 0, ',', '.')),

            ExportColumn::make('payment_status')
                ->label('Status pembayaran')
                ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? ''),

            ExportColumn::make('confirmer.name')
                ->label('Dikonfirmasi oleh'),

            ExportColumn::make('confirmed_at')
                ->label('Waktu konfirmasi')
                ->formatStateUsing(fn ($state): string => $state?->translatedFormat('d/m/Y H:i') ?? ''),

            ExportColumn::make('notes')
                ->label('Catatan'),

            ExportColumn::make('created_at')
                ->label('Waktu mendaftar')
                ->formatStateUsing(fn ($state): string => $state?->translatedFormat('d/m/Y H:i') ?? ''),
        ];
    }

    /**
     * Lihat catatan di FinanceTransactionExporter: export dijalankan langsung
     * agar tidak bergantung pada queue worker.
     */
    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Rekap zakat siap diunduh';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "{$export->successful_rows} pendaftaran berhasil diekspor.";

        if ($gagal = $export->getFailedRowsCount()) {
            $body .= " {$gagal} baris gagal diekspor.";
        }

        return $body;
    }
}
