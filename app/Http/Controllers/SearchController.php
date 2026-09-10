<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Pencarian global lintas modul (PRD 5.1.17).
 */
class SearchController extends Controller
{
    public function __invoke(Request $request, GlobalSearchService $search): View
    {
        $keyword = $request->string('q')->trim()->limit(100, '')->toString();
        $groups = $search->search($keyword);

        return view('public.cari', [
            'keyword' => $keyword,
            'groups' => $groups,
            'total' => $groups->sum(fn (array $group): int => $group['items']->count()),
        ]);
    }
}
