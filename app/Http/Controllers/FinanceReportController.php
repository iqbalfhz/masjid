<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\FinanceCategory;
use App\Models\MosqueSetting;
use App\Services\FinanceReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laporan keuangan publik (PRD 5.1.4) — dapat difilter dan diunduh sebagai PDF.
 */
class FinanceReportController extends Controller
{
    public function index(Request $request, FinanceReportService $service): View
    {
        $filters = $this->filters($request);

        return view('public.laporan-keuangan', [
            'filters' => $filters,
            'transactions' => $service->paginate($filters),
            'totals' => $service->totals($filters),
            'byCategory' => $service->byCategory($filters),
            'monthlySummary' => $service->monthlySummary(),
            'categories' => FinanceCategory::query()->orderBy('name')->get(),
            'types' => TransactionType::cases(),
        ]);
    }

    public function download(Request $request, FinanceReportService $service): Response
    {
        $filters = $this->filters($request);

        $pdf = Pdf::loadView('public.pdf.laporan-keuangan', [
            'setting' => MosqueSetting::current(),
            'filters' => $filters,
            'transactions' => $service->query($filters)->orderBy('date')->get(),
            'totals' => $service->totals($filters),
            'byCategory' => $service->byCategory($filters),
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('laporan-keuangan-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * @return array{dari: ?string, sampai: ?string, jenis: ?string, kategori: array<int, string>}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'jenis' => ['nullable', 'string', 'in:in,out'],
            'kategori' => ['nullable', 'array'],
            'kategori.*' => ['integer', 'exists:finance_categories,id'],
        ]);

        return [
            'dari' => $validated['dari'] ?? null,
            'sampai' => $validated['sampai'] ?? null,
            'jenis' => $validated['jenis'] ?? null,
            'kategori' => $validated['kategori'] ?? [],
        ];
    }
}
