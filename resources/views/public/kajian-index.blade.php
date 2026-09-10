@extends('layouts.public')

@section('title', 'Kajian & Majelis Ilmu')
@section('description', 'Daftar kajian rutin dan majelis ilmu di Masjid An-Nur Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Kajian & Majelis Ilmu"
        subtitle="Kajian rutin mingguan maupun kajian khusus. Sebagian kajian membuka konfirmasi kehadiran." />

    <x-public.card class="mb-6">
        <form method="get" class="grid gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="q" class="block text-sm font-medium text-masjid-800">Cari tema atau pemateri</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Contoh: tafsir, fiqih…"
                       class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
            </div>
            <div>
                <label for="hari" class="block text-sm font-medium text-masjid-800">Hari</label>
                <select id="hari" name="hari" class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    <option value="">Semua hari</option>
                    @foreach ($days as $value => $label)
                        <option value="{{ $value }}" @selected(request('hari') !== null && (string) request('hari') === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">Filter</button>
                @if (request()->hasAny(['q', 'hari', 'jenis']))
                    <a href="{{ route('kajian.index') }}" class="rounded-lg border border-masjid-200 px-4 py-2 text-sm text-masjid-700 hover:bg-masjid-50">Reset</a>
                @endif
            </div>
        </form>
    </x-public.card>

    @if ($studies->isEmpty())
        <x-public.empty-state message="Belum ada kajian yang cocok dengan pencarian Anda." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($studies as $study)
                <x-public.card as="article" class="flex flex-col">
                    <h2 class="font-semibold text-masjid-900">
                        <a href="{{ route('kajian.show', $study) }}" class="hover:underline">{{ $study->theme }}</a>
                    </h2>
                    <p class="mt-1 text-sm text-masjid-600">{{ $study->ustadz_name }}</p>
                    <p class="mt-2 text-sm text-masjid-700">{{ $study->scheduleLabel() }}</p>
                    <p class="mt-1 text-xs text-masjid-500">{{ $study->location }}</p>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($study->tags as $tag)
                            <a href="{{ route('tag.show', $tag) }}">
                                <x-public.badge color="gray">#{{ $tag->name }}</x-public.badge>
                            </a>
                        @endforeach
                        @if ($study->rsvp_enabled)
                            <x-public.badge color="emas">Terbuka RSVP</x-public.badge>
                        @endif
                    </div>
                </x-public.card>
            @endforeach
        </div>

        <div class="mt-8">{{ $studies->links() }}</div>
    @endif
@endsection
