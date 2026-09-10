<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\Study;
use App\Models\Tag;
use Illuminate\Contracts\View\View;

/**
 * Jelajah konten per tag lintas modul (PRD 5.1.18): satu tag bisa menempel di
 * kajian, kegiatan, artikel, maupun album galeri sekaligus.
 */
class TagController extends Controller
{
    public function __invoke(Tag $tag): View
    {
        return view('public.tag', [
            'tag' => $tag,
            'studies' => Study::query()->approved()->withAnyTags([$tag->name])->take(12)->get(),
            'events' => Event::query()->approved()->withAnyTags([$tag->name])->latest('event_date')->take(12)->get(),
            'articles' => Article::query()->published()->withAnyTags([$tag->name])->latest('publish_date')->take(12)->get(),
            'albums' => GalleryAlbum::query()->where('is_published', true)->withAnyTags([$tag->name])->take(12)->get(),
            'allTags' => Tag::query()->orderBy('name')->get(),
        ]);
    }
}
