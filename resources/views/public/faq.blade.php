@extends('layouts.public')

@section('title', 'Pertanyaan Umum (FAQ)')
@section('description', 'Jawaban atas pertanyaan yang sering diajukan jamaah Masjid An-Nur.')

@section('content')
    <x-public.page-header
        title="Pertanyaan Umum"
        subtitle="Jawaban atas hal-hal yang paling sering ditanyakan jamaah." />

    <x-public.card class="mb-6">
        <form method="get" class="flex flex-wrap items-end gap-3">
            <div class="min-w-56 flex-1">
                <label for="q" class="block text-sm font-medium text-masjid-800">Cari pertanyaan</label>
                <input id="q" type="search" name="q" value="{{ $keyword }}" placeholder="Contoh: donasi, fasilitas, kajian…"
                       class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
            </div>
            <button type="submit" class="rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">Cari</button>
            @if ($keyword !== '')
                <a href="{{ route('faq') }}" class="rounded-lg border border-masjid-200 px-4 py-2 text-sm text-masjid-700 hover:bg-masjid-50">Reset</a>
            @endif
        </form>
    </x-public.card>

    @if ($groupedFaqs->isEmpty())
        <x-public.empty-state message="Tidak ada pertanyaan yang cocok. Silakan kirim pertanyaan Anda lewat kotak saran." />
    @else
        <div class="space-y-8">
            @foreach ($groupedFaqs as $category => $faqs)
                <section>
                    <h2 class="mb-3 text-lg font-semibold text-masjid-900">{{ $category }}</h2>

                    <div class="space-y-3">
                        @foreach ($faqs as $faq)
                            <details id="faq-{{ $faq->id }}" class="group rounded-2xl border border-masjid-100 bg-white shadow-sm">
                                <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 font-medium text-masjid-900">
                                    {{ $faq->question }}
                                    <span class="shrink-0 text-masjid-400 transition group-open:rotate-45" aria-hidden="true">+</span>
                                </summary>
                                <div class="prose-masjid border-t border-masjid-100 px-5 py-4 text-masjid-700">
                                    {!! $faq->answer !!}
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    <x-public.card class="mt-8 bg-masjid-800 text-white">
        <h2 class="font-semibold">Pertanyaan Anda belum terjawab?</h2>
        <p class="mt-1 text-sm text-masjid-100">
            Kirimkan lewat kotak saran, pengurus akan menindaklanjuti. Anda juga bisa langsung menghubungi sekretariat DKM.
        </p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('saran.create') }}" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">Kirim pertanyaan</a>
            <a href="{{ route('kontak') }}" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-medium hover:bg-white/25">Hubungi pengurus</a>
        </div>
    </x-public.card>
@endsection
