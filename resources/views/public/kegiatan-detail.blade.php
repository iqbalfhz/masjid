@extends('layouts.public')

@section('title', $event->title)
@section('description', Str::limit(strip_tags($event->description ?? $event->title), 150))

@section('content')
    <nav aria-label="Breadcrumb" class="mb-4 text-sm text-masjid-600">
        <a href="{{ route('kegiatan.index') }}" class="hover:underline">Kegiatan</a>
        <span aria-hidden="true"> / </span>
        <span class="text-masjid-800">{{ $event->title }}</span>
    </nav>

    <div class="grid gap-8 grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <article>
            @if ($event->poster_image)
                <x-public.image :src="Storage::url($event->poster_image)" :alt="'Poster '.$event->title" class="mb-6 w-full rounded-2xl object-cover" />
            @endif

            <h1 class="text-2xl font-semibold text-masjid-900 sm:text-3xl">{{ $event->title }}</h1>

            <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                <x-public.card>
                    <dt class="text-xs uppercase tracking-wide text-masjid-500">Tanggal</dt>
                    <dd class="mt-1 text-sm font-medium text-masjid-800">{{ $event->event_date->translatedFormat('l, d F Y') }}</dd>
                </x-public.card>
                <x-public.card>
                    <dt class="text-xs uppercase tracking-wide text-masjid-500">Waktu</dt>
                    <dd class="mt-1 text-sm font-medium text-masjid-800">
                        {{ $event->start_time ? substr($event->start_time, 0, 5).' WIB' : 'Menyusul' }}
                        {{ $event->end_time ? '– '.substr($event->end_time, 0, 5) : '' }}
                    </dd>
                </x-public.card>
                <x-public.card>
                    <dt class="text-xs uppercase tracking-wide text-masjid-500">Lokasi</dt>
                    <dd class="mt-1 text-sm font-medium text-masjid-800">{{ $event->location }}</dd>
                </x-public.card>
            </dl>

            @if ($event->description)
                <div class="prose-masjid mt-8 text-masjid-800">{!! $event->description !!}</div>
            @endif

            @if ($event->tags->isNotEmpty())
                <div class="mt-6 flex flex-wrap gap-1.5">
                    @foreach ($event->tags as $tag)
                        <a href="{{ route('tag.show', $tag) }}"><x-public.badge color="gray">#{{ $tag->name }}</x-public.badge></a>
                    @endforeach
                </div>
            @endif
        </article>

        <aside class="space-y-6">
            @if ($event->rsvp_enabled && ! $event->event_date->isPast())
                @include('public.partials.rsvp-form', ['subject' => $event, 'jenis' => 'kegiatan', 'count' => $rsvpCount])
            @endif

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Butuh informasi lain?</h2>
                <p class="mt-1 text-sm text-masjid-600">
                    Hubungi sekretariat DKM di {{ $setting->phone ?? 'nomor kontak masjid' }}
                    atau kunjungi langsung {{ $setting->address }}.
                </p>
                <a href="{{ route('kontak') }}" class="mt-3 inline-block text-sm font-medium text-masjid-600 hover:underline">
                    Lihat kontak lengkap &rarr;
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
