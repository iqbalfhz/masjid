@extends('layouts.public')

@section('title', 'Galeri Kegiatan')
@section('description', 'Dokumentasi foto dan video kegiatan Masjid An-Nur Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Galeri Kegiatan"
        subtitle="Dokumentasi kegiatan masjid, dikelompokkan per album." />

    @if ($categories->isNotEmpty())
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('galeri.index') }}"
               @class([
                   'rounded-lg px-3 py-1.5 text-sm font-medium',
                   'bg-masjid-600 text-white' => ! request('kategori'),
                   'border border-masjid-200 bg-white text-masjid-700 hover:bg-masjid-50' => request('kategori'),
               ])>Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('galeri.index', ['kategori' => $category]) }}"
                   @class([
                       'rounded-lg px-3 py-1.5 text-sm font-medium',
                       'bg-masjid-600 text-white' => request('kategori') === $category,
                       'border border-masjid-200 bg-white text-masjid-700 hover:bg-masjid-50' => request('kategori') !== $category,
                   ])>{{ $category }}</a>
            @endforeach
        </div>
    @endif

    @if ($albums->isEmpty())
        <x-public.empty-state message="Belum ada album galeri yang dipublikasikan." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($albums as $album)
                <a href="{{ route('galeri.show', $album) }}"
                   class="group overflow-hidden rounded-2xl border border-masjid-100 bg-white shadow-sm transition hover:shadow-md">
                    <div class="overflow-hidden">
                        <img src="{{ $album->cover_image ? Storage::url($album->cover_image) : ($album->items->first()?->url() ?? asset('images/placeholder.svg')) }}"
                             alt="" class="h-44 w-full object-cover transition duration-300 group-hover:scale-105">
                    </div>
                    <div class="p-5">
                        @if ($album->category)
                            <x-public.badge color="gray">{{ $album->category }}</x-public.badge>
                        @endif
                        <h2 class="mt-2 font-semibold text-masjid-900 group-hover:underline">{{ $album->title }}</h2>
                        <p class="mt-1 text-xs text-masjid-500">
                            {{ $album->items_count }} dokumentasi
                            @if ($album->event_date)
                                &middot; {{ $album->event_date->translatedFormat('F Y') }}
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $albums->links() }}</div>
    @endif
@endsection
