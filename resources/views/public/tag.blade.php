@extends('layouts.public')

@section('title', 'Tag: '.$tag->name)
@section('description', 'Kumpulan kajian, kegiatan, artikel, dan galeri Masjid An-Nur dengan tag '.$tag->name)

@section('content')
    <x-public.page-header
        title="#{{ $tag->name }}"
        subtitle="Semua konten dari berbagai modul yang berkaitan dengan tag ini." />

    <div class="grid gap-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,1fr)]">
        <div class="space-y-10">
            @if ($studies->isEmpty() && $events->isEmpty() && $articles->isEmpty() && $albums->isEmpty())
                <x-public.empty-state message="Belum ada konten dengan tag ini." />
            @endif

            @if ($studies->isNotEmpty())
                <section>
                    <x-public.section-heading title="Kajian" :href="route('kajian.index')" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($studies as $study)
                            <x-public.card as="article">
                                <h3 class="font-semibold text-masjid-900">
                                    <a href="{{ route('kajian.show', $study) }}" class="hover:underline">{{ $study->theme }}</a>
                                </h3>
                                <p class="mt-1 text-sm text-masjid-600">{{ $study->ustadz_name }}</p>
                                <p class="mt-1 text-sm text-masjid-700">{{ $study->scheduleLabel() }}</p>
                            </x-public.card>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($events->isNotEmpty())
                <section>
                    <x-public.section-heading title="Kegiatan" :href="route('kegiatan.index')" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($events as $event)
                            <x-public.card as="article">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emas-600">
                                    {{ $event->event_date->translatedFormat('d F Y') }}
                                </p>
                                <h3 class="mt-1 font-semibold text-masjid-900">
                                    <a href="{{ route('kegiatan.show', $event) }}" class="hover:underline">{{ $event->title }}</a>
                                </h3>
                            </x-public.card>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($articles->isNotEmpty())
                <section>
                    <x-public.section-heading title="Artikel" :href="route('artikel.index')" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($articles as $article)
                            <x-public.card as="article">
                                <p class="text-xs text-masjid-500">{{ $article->publish_date->translatedFormat('d F Y') }}</p>
                                <h3 class="mt-1 font-semibold text-masjid-900">
                                    <a href="{{ route('artikel.show', $article) }}" class="hover:underline">{{ $article->title }}</a>
                                </h3>
                                <p class="mt-1 text-sm text-masjid-600">
                                    {{ Str::limit($article->excerpt ?? strip_tags($article->content), 100) }}
                                </p>
                            </x-public.card>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($albums->isNotEmpty())
                <section>
                    <x-public.section-heading title="Galeri" :href="route('galeri.index')" />
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach ($albums as $album)
                            <a href="{{ route('galeri.show', $album) }}" class="group overflow-hidden rounded-2xl border border-masjid-100 bg-white shadow-sm">
                                <x-public.image :src="$album->cover_image ? Storage::url($album->cover_image) : null" class="h-32 w-full object-cover transition group-hover:scale-105" />
                                <div class="p-4">
                                    <h3 class="font-medium text-masjid-900 group-hover:underline">{{ $album->title }}</h3>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside>
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Semua tag</h2>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($allTags as $item)
                        <a href="{{ route('tag.show', $item) }}">
                            <x-public.badge :color="$item->is($tag) ? 'emas' : 'gray'">#{{ $item->name }}</x-public.badge>
                        </a>
                    @endforeach
                </div>
            </x-public.card>
        </aside>
    </div>
@endsection
