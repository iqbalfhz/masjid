@extends('layouts.public')

@section('title', 'Profil Masjid')
@section('description', 'Sejarah, visi misi, dan struktur pengurus DKM Masjid An-Nur Tangcity Mall.')

@section('content')
    <x-public.page-header
        title="Profil Masjid"
        :subtitle="$setting->tagline" />

    <div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
        <div class="space-y-6">
            @if ($setting->history)
                <x-public.card>
                    <h2 class="text-lg font-semibold text-masjid-900">Sejarah</h2>
                    <div class="prose-masjid mt-3 text-masjid-800">{!! nl2br(e($setting->history)) !!}</div>
                </x-public.card>
            @endif

            <div class="grid gap-6 sm:grid-cols-2">
                @if ($setting->vision)
                    <x-public.card>
                        <h2 class="text-lg font-semibold text-masjid-900">Visi</h2>
                        <p class="mt-3 text-masjid-800">{{ $setting->vision }}</p>
                    </x-public.card>
                @endif

                @if ($setting->mission)
                    <x-public.card>
                        <h2 class="text-lg font-semibold text-masjid-900">Misi</h2>
                        <ol class="mt-3 list-decimal space-y-2 ps-5 text-masjid-800">
                            @foreach (preg_split('/\r\n|\r|\n/', $setting->mission) as $mission)
                                @if (trim($mission) !== '')
                                    <li>{{ trim($mission) }}</li>
                                @endif
                            @endforeach
                        </ol>
                    </x-public.card>
                @endif
            </div>

            <section>
                <x-public.section-heading title="Struktur Pengurus DKM" />

                @if ($boardMembers->isEmpty())
                    <x-public.empty-state message="Data pengurus sedang diperbarui." />
                @else
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($boardMembers as $member)
                            <x-public.card as="article" class="text-center">
                                <img src="{{ $member->photo ? Storage::url($member->photo) : asset('images/placeholder.svg') }}"
                                     alt="" class="mx-auto h-24 w-24 rounded-full object-cover">
                                <h3 class="mt-3 font-semibold text-masjid-900">{{ $member->name }}</h3>
                                <p class="text-sm text-masjid-600">{{ $member->position }}</p>
                                <p class="mt-1 text-xs text-masjid-500">Periode {{ $member->periodLabel() }}</p>
                                @if ($member->bio)
                                    <p class="mt-2 text-xs text-masjid-600">{{ $member->bio }}</p>
                                @endif
                            </x-public.card>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Identitas masjid</h2>
                <dl class="mt-3 space-y-3 text-sm">
                    <div>
                        <dt class="text-masjid-500">Nama</dt>
                        <dd class="font-medium text-masjid-900">{{ $setting->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-masjid-500">Alamat</dt>
                        <dd class="font-medium text-masjid-900">{{ $setting->address }}</dd>
                    </div>
                    @if ($setting->phone)
                        <div>
                            <dt class="text-masjid-500">Telepon</dt>
                            <dd class="font-medium text-masjid-900">{{ $setting->phone }}</dd>
                        </div>
                    @endif
                    @if ($setting->email)
                        <div>
                            <dt class="text-masjid-500">Email</dt>
                            <dd class="font-medium text-masjid-900">{{ $setting->email }}</dd>
                        </div>
                    @endif
                </dl>
            </x-public.card>

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Jelajahi lebih lanjut</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('kajian.index') }}" class="text-masjid-700 hover:underline">Kajian rutin</a></li>
                    <li><a href="{{ route('galeri.index') }}" class="text-masjid-700 hover:underline">Galeri kegiatan</a></li>
                    <li><a href="{{ route('laporan-keuangan') }}" class="text-masjid-700 hover:underline">Laporan keuangan</a></li>
                    <li><a href="{{ route('testimoni.index') }}" class="text-masjid-700 hover:underline">Buku tamu jamaah</a></li>
                </ul>
            </x-public.card>
        </aside>
    </div>
@endsection
