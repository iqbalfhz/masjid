@extends('layouts.public')

@section('title', $album->title)
@section('description', Str::limit($album->description ?? $album->title, 150))

@section('content')
    <nav aria-label="Breadcrumb" class="mb-4 text-sm text-masjid-600">
        <a href="{{ route('galeri.index') }}" class="hover:underline">Galeri</a>
        <span aria-hidden="true"> / </span>
        <span class="text-masjid-800">{{ $album->title }}</span>
    </nav>

    <x-public.page-header :title="$album->title" :subtitle="$album->description">
        <p class="mt-3 text-sm text-masjid-500">
            @if ($album->category)
                {{ $album->category }} &middot;
            @endif
            {{ $album->event_date?->translatedFormat('l, d F Y') ?? 'Tanggal tidak dicatat' }}
            &middot; {{ $album->items->count() }} dokumentasi
        </p>

        @if ($album->tags->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($album->tags as $tag)
                    <a href="{{ route('tag.show', $tag) }}"><x-public.badge color="gray">#{{ $tag->name }}</x-public.badge></a>
                @endforeach
            </div>
        @endif
    </x-public.page-header>

    @if ($album->items->isEmpty())
        <x-public.empty-state message="Album ini belum berisi dokumentasi." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($album->items as $item)
                <figure class="overflow-hidden rounded-2xl border border-masjid-100 bg-white shadow-sm">
                    @if ($item->type === \App\Enums\GalleryItemType::Video)
                        <a href="{{ $item->url() }}" target="_blank" rel="noopener noreferrer"
                           class="flex h-44 items-center justify-center bg-masjid-800 text-white">
                            <span class="text-center text-sm">
                                <span class="block text-3xl" aria-hidden="true">&#9654;</span>
                                Putar video
                            </span>
                        </a>
                    @else
                        <img src="{{ $item->url() ?? asset('images/placeholder.svg') }}"
                             alt="{{ $item->caption ?? 'Dokumentasi '.$album->title }}"
                             loading="lazy" class="h-44 w-full object-cover">
                    @endif

                    @if ($item->caption)
                        <figcaption class="p-4 text-sm text-masjid-600">{{ $item->caption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    @endif
@endsection
