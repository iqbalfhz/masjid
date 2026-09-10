<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Artikel publik (PRD 5.1.8) — hanya yang disetujui dan sudah lewat tanggal tayang.
 */
class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->string('q')->trim()->toString();

        $articles = Article::query()
            ->published()
            ->with(['category', 'tags'])
            ->when($request->filled('kategori'), fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $request->string('kategori')->toString())
            ))
            ->when($keyword !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('excerpt', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%")
            ))
            ->latest('publish_date')
            ->paginate(9)
            ->withQueryString();

        return view('public.artikel-index', [
            'articles' => $articles,
            'categories' => ArticleCategory::query()->withCount('articles')->orderBy('name')->get(),
            'keyword' => $keyword,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->increment('views');

        return view('public.artikel-detail', [
            'article' => $article->load(['category', 'tags', 'creator']),
            'related' => Article::query()
                ->published()
                ->whereKeyNot($article->getKey())
                ->where('article_category_id', $article->article_category_id)
                ->latest('publish_date')
                ->take(3)
                ->get(),
        ]);
    }
}
