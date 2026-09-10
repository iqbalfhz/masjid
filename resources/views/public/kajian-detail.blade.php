@extends('layouts.public')

@section('title', $study->theme)
@section('description', Str::limit(strip_tags($study->description ?? $study->theme), 150))

@section('content')
    <nav aria-label="Breadcrumb" class="mb-4 text-sm text-masjid-600">
        <a href="{{ route('kajian.index') }}" class="hover:underline">Kajian</a>
        <span aria-hidden="true"> / </span>
        <span class="text-masjid-800">{{ $study->theme }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <article>
            @if ($study->poster_image)
                <x-public.image :src="Storage::url($study->poster_image)" :alt="'Poster '.$study->theme" class="mb-6 w-full rounded-2xl object-cover" />
            @endif

            <h1 class="text-2xl font-semibold text-masjid-900 sm:text-3xl">{{ $study->theme }}</h1>
            <p class="mt-2 text-masjid-600">Bersama {{ $study->ustadz_name }}</p>

            <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                <x-public.card>
                    <dt class="text-xs uppercase tracking-wide text-masjid-500">Jadwal</dt>
                    <dd class="mt-1 text-sm font-medium text-masjid-800">{{ $study->scheduleLabel() }}</dd>
                </x-public.card>
                <x-public.card>
                    <dt class="text-xs uppercase tracking-wide text-masjid-500">Lokasi</dt>
                    <dd class="mt-1 text-sm font-medium text-masjid-800">{{ $study->location }}</dd>
                </x-public.card>
                <x-public.card>
                    <dt class="text-xs uppercase tracking-wide text-masjid-500">Konfirmasi hadir</dt>
                    <dd class="mt-1 text-sm font-medium text-masjid-800">
                        {{ $study->rsvp_enabled ? $rsvpCount.' jamaah' : 'Terbuka untuk umum' }}
                    </dd>
                </x-public.card>
            </dl>

            @if ($study->description)
                <div class="prose-masjid mt-8 text-masjid-800">{!! nl2br(e($study->description)) !!}</div>
            @endif

            @if ($study->tags->isNotEmpty())
                <div class="mt-6 flex flex-wrap gap-1.5">
                    @foreach ($study->tags as $tag)
                        <a href="{{ route('tag.show', $tag) }}"><x-public.badge color="gray">#{{ $tag->name }}</x-public.badge></a>
                    @endforeach
                </div>
            @endif

            @if ($study->materials->isNotEmpty())
                <section class="mt-10">
                    <x-public.section-heading title="Materi kajian ini" :href="route('e-library')" />
                    <ul class="grid gap-3 sm:grid-cols-2">
                        @foreach ($study->materials as $material)
                            <li>
                                <x-public.card>
                                    <p class="text-xs uppercase tracking-wide text-masjid-500">{{ $material->type->getLabel() }}</p>
                                    <h3 class="mt-1 font-medium text-masjid-900">{{ $material->title }}</h3>
                                    <a href="{{ route('e-library.unduh', $material) }}" class="mt-2 inline-block text-sm font-medium text-masjid-600 hover:underline">
                                        Buka materi &rarr;
                                    </a>
                                </x-public.card>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </article>

        <aside class="space-y-6">
            @if ($study->rsvp_enabled)
                @include('public.partials.rsvp-form', ['subject' => $study, 'jenis' => 'kajian', 'count' => $rsvpCount])
            @endif

            @if ($related->isNotEmpty())
                <x-public.card>
                    <h2 class="font-semibold text-masjid-900">Kajian lain oleh pemateri ini</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($related as $item)
                            <li>
                                <a href="{{ route('kajian.show', $item) }}" class="text-masjid-700 hover:underline">{{ $item->theme }}</a>
                                <p class="text-xs text-masjid-500">{{ $item->scheduleLabel() }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-public.card>
            @endif
        </aside>
    </div>
@endsection
