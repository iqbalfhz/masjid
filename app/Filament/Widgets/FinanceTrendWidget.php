<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\FinanceTransaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Pemasukan dan pengeluaran enam bulan terakhir.
 *
 * Angka satu bulan tidak bisa jadi dasar keputusan: "Rp 21.532.000" itu naik
 * atau turun? Laporan keuangan adalah modul inti yang bahkan punya fitur export,
 * jadi pengurus perlu melihat arahnya, bukan cuma posisi terakhir.
 */
class FinanceTrendWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Tren Keuangan';

    protected ?string $description = 'Pemasukan dan pengeluaran enam bulan terakhir.';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->can('view_any:finance_transaction') === true;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $ago): Carbon => today()->startOfMonth()->subMonths($ago));

        $income = [];
        $expense = [];
        $labels = [];

        foreach ($months as $month) {
            $labels[] = $month->translatedFormat('M Y');

            $income[] = (float) FinanceTransaction::query()
                ->inMonth($month->year, $month->month)
                ->where('type', TransactionType::In)
                ->sum('amount');

            $expense[] = (float) FinanceTransaction::query()
                ->inMonth($month->year, $month->month)
                ->where('type', TransactionType::Out)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $income,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expense,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
