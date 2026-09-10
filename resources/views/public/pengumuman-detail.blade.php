@extends('layouts.public')

@section('title', $announcement->title)
@section('description', Str::limit(strip_tags($announcement->content), 150))

@section('content')
    <nav aria-label="Breadcrumb" class="mb-4 text-sm text-masjid-600">
        <a href="{{ route('home') }}" class="hover:underline">Beranda</a>
        <span aria-hidden="true"> / </span>
        <span class="text-masjid-800">Pengumuman</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <article>
            <div class="flex flex-wrap items-center gap-3 text-sm text-masjid-500">
                <x-public.badge :color="$announcement->priority === \App\Enums\AnnouncementPriority::Tinggi ? 'emas' : 'gray'">
                    Prioritas {{ $announcement->priority->getLabel() }}
                </x-public.badge>
                <span>Berlaku sejak {{ $announcement->start_date->translatedFormat('d F Y') }}</span>
                @if ($announcement->end_date)
                    <span aria-hidden="true">&middot;</span>
                    <span>Sampai {{ $announcement->end_date->translatedFormat('d F Y') }}</span>
                @endif
            </div>

            <h1 class="mt-3 text-2xl font-semibold text-masjid-900 sm:text-3xl">{{ $announcement->title }}</h1>

            <div class="prose-masjid mt-6 text-masjid-800">{!! $announcement->content !!}</div>

            <p class="mt-8 border-t border-masjid-100 pt-4 text-xs text-masjid-500">
                Diumumkan oleh Tim DKM {{ $setting->name }}.
            </p>
        </article>

        <aside class="space-y-6">
            @if ($others->isNotEmpty())
                <x-public.card>
                    <h2 class="font-semibold text-masjid-900">Pengumuman lain</h2>
                    <ul class="mt-3 space-y-3 text-sm">
                        @foreach ($others as $item)
                            <li>
                                <a href="{{ route('pengumuman.show', $item) }}" class="font-medium text-masjid-700 hover:underline">
                                    {{ $item->title }}
                                </a>
                                <p class="text-xs text-masjid-500">{{ $item->start_date->translatedFormat('d F Y') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-public.card>
            @endif

            <x-public.card tone="dark">
                <h2 class="font-semibold">Jangan sampai ketinggalan</h2>
                <p class="mt-1 text-sm text-masjid-100">
                    Aktifkan pengingat waktu sholat sekaligus pantau pengumuman terbaru dari beranda.
                </p>
                <a href="{{ route('jadwal-sholat') }}" class="mt-3 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">
                    Atur pengingat
                </a>
            </x-public.card>
        </aside>
    </div>
@endsection
