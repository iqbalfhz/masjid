@extends('layouts.public')

@section('title', $article->title)
@section('description', Str::limit($article->excerpt ?? strip_tags($article->content), 150))

@section('content')
    <nav aria-label="Breadcrumb" class="mb-4 text-sm text-masjid-600">
        <a href="{{ route('artikel.index') }}" class="hover:underline">Artikel</a>
        <span aria-hidden="true"> / </span>
        <span class="text-masjid-800">{{ Str::limit($article->title, 60) }}</span>
    </nav>

    <div class="grid gap-8 grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <article>
            @if ($article->cover_image)
                <x-public.image :src="Storage::url($article->cover_image)" class="mb-6 w-full rounded-2xl object-cover" />
            @endif

            <div class="flex flex-wrap items-center gap-3 text-sm text-masjid-500">
                @if ($article->category)
                    <x-public.badge color="gray">{{ $article->category->name }}</x-public.badge>
                @endif
                <span>{{ $article->publish_date->translatedFormat('l, d F Y') }}</span>
                <span aria-hidden="true">&middot;</span>
                <span>{{ number_format($article->views, 0, ',', '.') }} kali dibaca</span>
            </div>

            <h1 class="mt-3 text-2xl font-semibold text-masjid-900 sm:text-3xl">{{ $article->title }}</h1>

            @if ($article->excerpt)
                <p class="mt-3 text-lg text-masjid-700">{{ $article->excerpt }}</p>
            @endif

            <div class="prose-masjid mt-6 text-masjid-800">{!! $article->content !!}</div>

            @if ($article->tags->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-1.5 border-t border-masjid-100 pt-6">
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('tag.show', $tag) }}"><x-public.badge color="gray">#{{ $tag->name }}</x-public.badge></a>
                    @endforeach
                </div>
            @endif
        </article>

        <aside class="space-y-6">
            @if ($related->isNotEmpty())
                <x-public.card>
                    <h2 class="font-semibold text-masjid-900">Artikel terkait</h2>
                    <ul class="mt-3 space-y-3 text-sm">
                        @foreach ($related as $item)
                            <li>
                                <a href="{{ route('artikel.show', $item) }}" class="font-medium text-masjid-700 hover:underline">
                                    {{ $item->title }}
                                </a>
                                <p class="text-xs text-masjid-500">{{ $item->publish_date->translatedFormat('d F Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-public.card>
            @endif

            <x-public.card tone="dark">
                <h2 class="font-semibold">Ikut kajian langsung</h2>
                <p class="mt-1 text-sm text-masjid-100">
                    Belajar lebih dalam bersama para ustadz di majelis ilmu Masjid An-Nur.
                </p>
                <a href="{{ route('kajian.index') }}" class="mt-3 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">
                    Lihat jadwal kajian
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
