<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\FinanceTransaction;
use Illuminate\Contracts\View\View;

/**
 * Halaman donasi (PRD 5.1.5): rekening resmi dan QRIS, tanpa payment gateway.
 */
class DonationController extends Controller
{
    public function __invoke(): View
    {
        return view('public.donasi', [
            'monthlyIncome' => (float) FinanceTransaction::query()
                ->inMonth(today()->year, today()->month)
                ->where('type', TransactionType::In)
                ->sum('amount'),
        ]);
    }
}
