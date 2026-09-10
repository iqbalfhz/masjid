@extends('layouts.public')

@section('title', 'Jadwal Sholat')
@section('description', 'Jadwal sholat bulanan Masjid An-Nur Tangcity Mall, lengkap dengan pengingat otomatis.')

@section('content')
    <x-public.page-header
        title="Jadwal Sholat"
        subtitle="Jadwal dihitung otomatis untuk titik koordinat Masjid An-Nur dan diperbarui setiap hari." />

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div>
            <x-public.card class="mb-6">
                <form method="get" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="bulan" class="block text-sm font-medium text-masjid-800">Pilih bulan</label>
                        <input id="bulan" type="month" name="bulan" value="{{ $month->format('Y-m') }}"
                               class="mt-1 rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    </div>
                    <button type="submit" class="rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">
                        Tampilkan
                    </button>
                </form>
            </x-public.card>

            <x-public.card class="!p-0">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <caption class="px-5 py-4 text-left text-base font-semibold text-masjid-900">
                            Jadwal {{ $month->translatedFormat('F Y') }}
                        </caption>
                        <thead class="bg-masjid-50 text-left text-xs uppercase tracking-wide text-masjid-600">
                            <tr>
                                <th scope="col" class="px-5 py-3">Tanggal</th>
                                @foreach (\App\Models\PrayerSchedule::PRAYERS as $label)
                                    <th scope="col" class="px-3 py-3">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-masjid-100">
                            @forelse ($schedules as $schedule)
                                <tr @class(['bg-emas-50' => $schedule->date->isToday()])>
                                    <th scope="row" class="px-5 py-2.5 text-left font-medium text-masjid-800">
                                        {{ $schedule->date->translatedFormat('D, d M') }}
                                        @if ($schedule->date->isToday())
                                            <span class="ms-1 text-xs font-semibold text-emas-700">(hari ini)</span>
                                        @endif
                                        @if ($schedule->is_override)
                                            <span class="ms-1 text-xs font-normal text-masjid-500" title="Disesuaikan manual oleh pengurus">*</span>
                                        @endif
                                    </th>
                                    @foreach (array_keys(\App\Models\PrayerSchedule::PRAYERS) as $key)
                                        <td class="px-3 py-2.5 tabular-nums text-masjid-700">
                                            {{ $schedule->{$key} ? substr($schedule->{$key}, 0, 5) : '—' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-masjid-500">
                                        Jadwal untuk bulan ini belum tersedia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="border-t border-masjid-100 px-5 py-3 text-xs text-masjid-500">
                    Tanda (*) menandakan jadwal yang disesuaikan manual oleh pengurus masjid.
                </p>
            </x-public.card>
        </div>

        <aside class="space-y-6">
            @if ($todaySchedule)
                <x-public.card tone="dark">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-emas-300">Hari ini</h2>
                    <p class="mt-1 text-sm text-masjid-100">{{ today()->translatedFormat('l, d F Y') }}</p>

                    @if ($nextPrayer)
                        <p class="mt-4 text-sm text-masjid-100">Waktu berikutnya</p>
                        <p class="text-2xl font-semibold">{{ $nextPrayer['label'] }} &middot; {{ $nextPrayer['time']->format('H:i') }}</p>
                    @endif

                    <dl class="mt-4 space-y-1.5 text-sm">
                        @foreach ($todaySchedule->times() as $key => $time)
                            <div class="flex items-center justify-between border-b border-white/10 pb-1.5 last:border-0">
                                <dt class="text-masjid-100">{{ \App\Models\PrayerSchedule::PRAYERS[$key] }}</dt>
                                <dd class="font-medium tabular-nums">{{ substr($time, 0, 5) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-public.card>
            @endif

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Pengingat waktu sholat</h2>

                @if ($reminderEnabled)
                    <p class="mt-1 text-sm text-masjid-600">
                        Aktifkan notifikasi browser untuk diingatkan menjelang waktu sholat. Tidak perlu memasang aplikasi apa pun.
                    </p>

                    <fieldset class="mt-4">
                        <legend class="text-sm font-medium text-masjid-800">Waktu yang ingin diingatkan</legend>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                            @foreach (\App\Models\PrayerSchedule::REMINDABLE as $key => $label)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" value="{{ $key }}" data-push-prayer
                                           @checked(in_array($key, $reminderPrayers, true))
                                           class="rounded border-masjid-300 text-masjid-600 focus:ring-masjid-400">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mt-4">
                        <label for="push-minutes" class="block text-sm font-medium text-masjid-800">Ingatkan berapa menit sebelumnya</label>
                        <select id="push-minutes" data-push-minutes
                                class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                            @foreach ([5, 10, 15, 30] as $minutes)
                                <option value="{{ $minutes }}" @selected($minutes === 10)>{{ $minutes }} menit</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" data-push-toggle data-active="false"
                            class="mt-4 w-full rounded-lg bg-masjid-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-masjid-700 disabled:opacity-60">
                        Aktifkan pengingat sholat
                    </button>

                    <p data-push-status role="status" class="mt-2 text-xs text-masjid-600"></p>
                @else
                    <p class="mt-1 text-sm text-masjid-600">
                        Fitur pengingat sedang dinonaktifkan oleh pengurus masjid.
                    </p>
                @endif
            </x-public.card>

            <x-public.card>
                <h2 class="font-semibold text-masjid-900">Jadwal khusus</h2>
                <ul class="mt-2 space-y-2 text-sm text-masjid-700">
                    <li><strong class="font-medium">Sholat Jumat:</strong> khutbah dimulai pukul 12.00 WIB.</li>
                    <li><strong class="font-medium">Sholat Id:</strong> diumumkan menjelang Idul Fitri dan Idul Adha.</li>
                    <li><strong class="font-medium">Tarawih:</strong> berjamaah setelah Isya selama Ramadhan.</li>
                </ul>
                <p class="mt-3 text-xs text-masjid-500">
                    Perubahan jadwal khusus diumumkan lewat <a href="{{ route('home') }}" class="underline">pengumuman</a> di beranda.
                </p>
            </x-public.card>
        </aside>
    </div>
@endsection
