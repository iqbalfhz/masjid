<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Galeri foto & video kegiatan (PRD 5.1.7).
 */
class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $albums = GalleryAlbum::query()
            ->where('is_published', true)
            ->withCount('items')
            ->with('items')
            ->when($request->filled('kategori'), fn ($query) => $query->where('category', $request->string('kategori')->toString()))
            ->latest('event_date')
            ->paginate(9)
            ->withQueryString();

        return view('public.galeri-index', [
            'albums' => $albums,
            'categories' => GalleryAlbum::query()
                ->where('is_published', true)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category'),
        ]);
    }

    public function show(GalleryAlbum $album): View
    {
        abort_unless($album->is_published, 404);

        return view('public.galeri-detail', [
            'album' => $album->load(['items', 'tags']),
        ]);
    }
}
