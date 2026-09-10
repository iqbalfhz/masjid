@extends('layouts.public')

@section('title', 'E-Library')
@section('description', 'Arsip materi kajian Masjid An-Nur: slide, dokumen, rekaman audio, dan video.')

@section('content')
    <x-public.page-header
        title="E-Library"
        subtitle="Arsip materi kajian yang sudah berlalu. Anda bisa membaca, mengunduh, atau memutarnya kapan saja." />

    <x-public.card class="mb-6">
        <form method="get" class="grid gap-3 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="q" class="block text-sm font-medium text-masjid-800">Cari materi</label>
                <input id="q" type="search" name="q" value="{{ $keyword }}" placeholder="Judul, pemateri, atau kata kunci"
                       class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
            </div>
            <div>
                <label for="jenis" class="block text-sm font-medium text-masjid-800">Jenis materi</label>
                <select id="jenis" name="jenis" class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    <option value="">Semua jenis</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(request('jenis') === $type->value)>{{ $type->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kajian" class="block text-sm font-medium text-masjid-800">Kajian</label>
                <select id="kajian" name="kajian" class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    <option value="">Semua kajian</option>
                    @foreach ($studies as $study)
                        <option value="{{ $study->id }}" @selected((string) request('kajian') === (string) $study->id)>{{ $study->theme }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 lg:col-span-4">
                <button type="submit" class="rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">Terapkan filter</button>
                @if (request()->hasAny(['q', 'jenis', 'kajian']))
                    <a href="{{ route('e-library') }}" class="rounded-lg border border-masjid-200 px-4 py-2 text-sm text-masjid-700 hover:bg-masjid-50">Reset</a>
                @endif
            </div>
        </form>
    </x-public.card>

    @if ($materials->isEmpty())
        <x-public.empty-state message="Belum ada materi yang cocok dengan filter Anda." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($materials as $material)
                <x-public.card as="article" class="flex flex-col">
                    <div class="flex items-center justify-between gap-2">
                        <x-public.badge color="emas">{{ $material->type->getLabel() }}</x-public.badge>
                        @if ($material->material_date)
                            <span class="text-xs text-masjid-500">{{ $material->material_date->translatedFormat('d M Y') }}</span>
                        @endif
                    </div>

                    <h2 class="mt-3 font-semibold text-masjid-900">{{ $material->title }}</h2>

                    @if ($material->ustadz_name)
                        <p class="mt-1 text-sm text-masjid-600">{{ $material->ustadz_name }}</p>
                    @endif

                    @if ($material->study)
                        <p class="mt-1 text-xs text-masjid-500">
                            Dari kajian
                            <a href="{{ route('kajian.show', $material->study) }}" class="hover:underline">{{ $material->study->theme }}</a>
                        </p>
                    @endif

                    @if ($material->description)
                        <p class="mt-2 flex-1 text-sm text-masjid-600">{{ Str::limit($material->description, 110) }}</p>
                    @endif

                    <a href="{{ route('e-library.unduh', $material) }}"
                       @if ($material->external_url) target="_blank" rel="noopener noreferrer" @endif
                       class="mt-4 inline-flex items-center justify-center rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">
                        {{ in_array($material->type, [\App\Enums\MaterialType::Audio, \App\Enums\MaterialType::Video], true) ? 'Putar materi' : 'Buka materi' }}
                    </a>
                </x-public.card>
            @endforeach
        </div>

        <div class="mt-8">{{ $materials->links() }}</div>
    @endif
@endsection
