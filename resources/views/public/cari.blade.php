@extends('layouts.public')

@section('title', $keyword !== '' ? 'Hasil pencarian: '.$keyword : 'Pencarian')
@section('description', 'Cari kajian, artikel, pengumuman, FAQ, dan materi e-library Masjid An-Nur dalam satu tempat.')

@section('content')
    <x-public.page-header
        title="Pencarian"
        subtitle="Satu kotak pencarian untuk kajian, kegiatan, artikel, pengumuman, FAQ, dan materi e-library." />

    <x-public.card class="mb-8">
        <form method="get" role="search" class="flex flex-wrap items-end gap-3">
            <div class="min-w-56 flex-1">
                <label for="q" class="block text-sm font-medium text-masjid-800">Kata kunci</label>
                <input id="q" type="search" name="q" value="{{ $keyword }}" autofocus
                       placeholder="Contoh: ramadhan, zakat, tafsir…"
                       class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
            </div>
            <button type="submit" class="rounded-lg bg-masjid-600 px-5 py-2 text-sm font-semibold text-white hover:bg-masjid-700">Cari</button>
        </form>
    </x-public.card>

    @if ($keyword === '')
        <x-public.empty-state message="Masukkan kata kunci untuk mulai mencari." />
    @elseif ($groups->isEmpty())
        <x-public.empty-state message="Tidak ditemukan hasil untuk “{{ $keyword }}”. Coba kata kunci lain yang lebih umum." />
    @else
        <p class="mb-6 text-sm text-masjid-600">
            Ditemukan <strong class="text-masjid-800">{{ $total }}</strong> hasil untuk “{{ $keyword }}”.
        </p>

        <div class="space-y-8">
            @foreach ($groups as $group)
                <section>
                    <h2 class="mb-3 text-lg font-semibold text-masjid-900">
                        {{ $group['label'] }}
                        <span class="text-sm font-normal text-masjid-500">({{ $group['items']->count() }})</span>
                    </h2>

                    <ul class="space-y-3">
                        @foreach ($group['items'] as $item)
                            <li>
                                <x-public.card as="article">
                                    <h3 class="font-medium text-masjid-900">
                                        <a href="{{ $item['url'] }}" class="hover:underline">{{ $item['title'] }}</a>
                                    </h3>
                                    @if ($item['snippet'] !== '')
                                        <p class="mt-1 text-sm text-masjid-600">{{ $item['snippet'] }}</p>
                                    @endif
                                </x-public.card>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
@endsection
