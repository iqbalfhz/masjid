@extends('layouts.public')

@section('title', 'Artikel')
@section('description', 'Artikel Islami dan informasi seputar Masjid An-Nur Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Artikel"
        subtitle="Tulisan seputar ilmu Islam dan kabar terbaru dari Masjid An-Nur." />

    <div class="grid gap-8 grid-cols-1 lg:grid-cols-[minmax(0,3fr)_minmax(0,1fr)]">
        <div>
            <x-public.card class="mb-6">
                <form method="get" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-56 flex-1">
                        <label for="q" class="block text-sm font-medium text-masjid-800">Cari judul atau kata kunci</label>
                        <input id="q" type="search" name="q" value="{{ $keyword }}" placeholder="Contoh: sholat, ramadhan…"
                               class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    </div>
                    @if (request('kategori'))
                        <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                    @endif
                    <button type="submit" class="rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">Cari</button>
                    @if ($keyword !== '' || request('kategori'))
                        <a href="{{ route('artikel.index') }}" class="rounded-lg border border-masjid-200 px-4 py-2 text-sm text-masjid-700 hover:bg-masjid-50">Reset</a>
                    @endif
                </form>
            </x-public.card>

            @if ($articles->isEmpty())
                <x-public.empty-state message="Belum ada artikel yang cocok dengan pencarian Anda." />
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($articles as $article)
                        <x-public.card as="article" class="flex flex-col overflow-hidden p-0!">
                            <x-public.image :src="$article->cover_image ? Storage::url($article->cover_image) : null" class="h-40 w-full object-cover" />
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-masjid-500">
                                    @if ($article->category)
                                        <x-public.badge color="gray">{{ $article->category->name }}</x-public.badge>
                                    @endif
                                    <span>{{ $article->publish_date->translatedFormat('d F Y') }}</span>
                                </div>

                                <h2 class="mt-2 font-semibold text-masjid-900">
                                    <a href="{{ route('artikel.show', $article) }}" class="hover:underline">{{ $article->title }}</a>
                                </h2>

                                <p class="mt-2 flex-1 text-sm text-masjid-600">
                                    {{ Str::limit($article->excerpt ?? strip_tags($article->content), 130) }}
                                </p>

                                <a href="{{ route('artikel.show', $article) }}" class="mt-3 text-sm font-medium text-masjid-600 hover:underline">
                                    Baca selengkapnya &rarr;
                                </a>
                            </div>
                        </x-public.card>
                    @endforeach
                </div>

                <div class="mt-8">{{ $articles->links() }}</div>
            @endif
        </div>

        <aside>
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Kategori</h2>
                <ul class="mt-3 space-y-1.5 text-sm">
                    <li>
                        <a href="{{ route('artikel.index') }}"
                           @class(['hover:underline', 'font-semibold text-masjid-800' => ! request('kategori'), 'text-masjid-700' => request('kategori')])>
                            Semua artikel
                        </a>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('artikel.index', ['kategori' => $category->slug]) }}"
                               @class([
                                   'hover:underline',
                                   'font-semibold text-masjid-800' => request('kategori') === $category->slug,
                                   'text-masjid-700' => request('kategori') !== $category->slug,
                               ])>
                                {{ $category->name }}
                                <span class="text-xs text-masjid-500">({{ $category->articles_count }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-public.card>
        </aside>
    </div>
@endsection
