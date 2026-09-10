<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * FAQ yang dikelompokkan per kategori dan bisa dicari (PRD 5.1.15).
 */
class FaqController extends Controller
{
    public function __invoke(Request $request): View
    {
        $keyword = $request->string('q')->trim()->toString();

        $faqs = Faq::query()
            ->published()
            ->when($keyword !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('question', 'like', "%{$keyword}%")->orWhere('answer', 'like', "%{$keyword}%")
            ))
            ->get()
            ->groupBy('category');

        return view('public.faq', [
            'groupedFaqs' => $faqs,
            'keyword' => $keyword,
        ]);
    }
}
