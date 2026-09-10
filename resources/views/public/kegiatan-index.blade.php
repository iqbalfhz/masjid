@extends('layouts.public')

@section('title', 'Kegiatan Masjid')
@section('description', 'Kalender kegiatan Masjid An-Nur Tangcity Mall: kegiatan sosial, hari besar Islam, dan program jamaah.')

@section('content')
    <x-public.page-header
        title="Kegiatan Masjid"
        subtitle="Agenda kegiatan yang akan datang beserta arsip kegiatan yang sudah terlaksana." />

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="inline-flex rounded-lg border border-masjid-200 bg-white p-1 text-sm">
            <a href="{{ route('kegiatan.index') }}"
               @class([
                   'rounded-md px-3 py-1.5 font-medium',
                   'bg-masjid-600 text-white' => ! $showPast,
                   'text-masjid-700 hover:bg-masjid-50' => $showPast,
               ])>Akan datang</a>
            <a href="{{ route('kegiatan.index', ['arsip' => 1]) }}"
               @class([
                   'rounded-md px-3 py-1.5 font-medium',
                   'bg-masjid-600 text-white' => $showPast,
                   'text-masjid-700 hover:bg-masjid-50' => ! $showPast,
               ])>Arsip</a>
        </div>

        @if ($categories->isNotEmpty())
            <form method="get" class="flex items-center gap-2">
                @if ($showPast)
                    <input type="hidden" name="arsip" value="1">
                @endif
                <label for="kategori" class="text-sm font-medium text-masjid-800">Kategori</label>
                <select id="kategori" name="kategori" onchange="this.form.submit()"
                        class="rounded-lg border border-masjid-200 px-3 py-1.5 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    <option value="">Semua</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('kategori') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if ($events->isEmpty())
        <x-public.empty-state message="{{ $showPast ? 'Belum ada arsip kegiatan.' : 'Belum ada kegiatan yang dijadwalkan.' }}" />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
                <x-public.card as="article" class="flex flex-col overflow-hidden !p-0">
                    <x-public.image :src="$event->poster_image ? Storage::url($event->poster_image) : null" class="h-40 w-full object-cover" />
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emas-600">
                            {{ $event->event_date->translatedFormat('l, d F Y') }}
                        </p>
                        <h2 class="mt-1 font-semibold text-masjid-900">
                            <a href="{{ route('kegiatan.show', $event) }}" class="hover:underline">{{ $event->title }}</a>
                        </h2>
                        <p class="mt-2 flex-1 text-sm text-masjid-600">
                            {{ Str::limit(strip_tags($event->description ?? ''), 100) }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @if ($event->category)
                                <x-public.badge>{{ $event->category }}</x-public.badge>
                            @endif
                            @if ($event->rsvp_enabled)
                                <x-public.badge color="emas">Terbuka RSVP</x-public.badge>
                            @endif
                        </div>
                    </div>
                </x-public.card>
            @endforeach
        </div>

        <div class="mt-8">{{ $events->links() }}</div>
    @endif
@endsection
