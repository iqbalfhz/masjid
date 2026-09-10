<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\FinanceTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Laporan keuangan publik (PRD 5.1.4): filter multi-dimensi, ringkasan
 * bulanan, dan rincian per kategori.
 *
 * @phpstan-type ReportFilters array{dari?: ?string, sampai?: ?string, jenis?: ?string, kategori?: ?array<int, int|string>}
 */
class FinanceReportService
{
    /**
     * @param  ReportFilters  $filters
     * @return Builder<FinanceTransaction>
     */
    public function query(array $filters): Builder
    {
        return FinanceTransaction::query()
            ->with('category')
            ->when($filters['dari'] ?? null, fn (Builder $query, string $date) => $query->whereDate('date', '>=', $date))
            ->when($filters['sampai'] ?? null, fn (Builder $query, string $date) => $query->whereDate('date', '<=', $date))
            ->when($filters['jenis'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when(
                filled($filters['kategori'] ?? null),
                fn (Builder $query) => $query->whereIn('finance_category_id', (array) $filters['kategori']),
            );
    }

    /**
     * @param  ReportFilters  $filters
     * @return LengthAwarePaginator<int, FinanceTransaction>
     */
    public function paginate(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->query($filters)->latest('date')->latest('id')->paginate($perPage)->withQueryString();
    }

    /**
     * @param  ReportFilters  $filters
     * @return array{income: float, expense: float, balance: float, count: int}
     */
    public function totals(array $filters): array
    {
        $income = (float) $this->query($filters)->where('type', TransactionType::In)->sum('amount');
        $expense = (float) $this->query($filters)->where('type', TransactionType::Out)->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'count' => $this->query($filters)->count(),
        ];
    }

    /**
     * Rekap per kategori untuk tabel rincian.
     *
     * @param  ReportFilters  $filters
     * @return Collection<int, object{name: string, type: TransactionType, total: float}>
     */
    public function byCategory(array $filters): Collection
    {
        return $this->query($filters)
            ->get()
            ->groupBy('finance_category_id')
            ->map(fn (Collection $rows): object => (object) [
                'name' => $rows->first()->category?->name ?? 'Tanpa kategori',
                'type' => $rows->first()->type,
                'total' => (float) $rows->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Ringkasan 12 bulan terakhir untuk grafik/tabel bulanan.
     *
     * @return Collection<int, object{month: string, income: float, expense: float, balance: float}>
     */
    public function monthlySummary(int $months = 12): Collection
    {
        $start = today()->subMonths($months - 1)->startOfMonth();

        $rows = FinanceTransaction::query()
            ->whereDate('date', '>=', $start)
            ->get()
            ->groupBy(fn (FinanceTransaction $transaction): string => $transaction->date->format('Y-m'));

        return collect(range(0, $months - 1))
            ->map(function (int $offset) use ($start, $rows): object {
                $month = $start->copy()->addMonths($offset);
                $group = $rows->get($month->format('Y-m'), collect());

                $income = (float) $group->where('type', TransactionType::In)->sum('amount');
                $expense = (float) $group->where('type', TransactionType::Out)->sum('amount');

                return (object) [
                    'month' => $month->translatedFormat('F Y'),
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense,
                ];
            })
            ->reverse()
            ->values();
    }
}
