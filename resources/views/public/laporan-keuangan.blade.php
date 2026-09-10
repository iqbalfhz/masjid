@extends('layouts.public')

@section('title', 'Laporan Keuangan')
@section('description', 'Laporan pemasukan dan pengeluaran Masjid An-Nur secara terbuka, dapat difilter per periode dan kategori.')

@section('content')
    <x-public.page-header
        title="Laporan Keuangan"
        subtitle="Seluruh pemasukan dan pengeluaran masjid dipublikasikan terbuka. Gunakan filter untuk melihat periode atau kategori tertentu." />

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        <x-public.card>
            <p class="text-sm text-masjid-600">Total pemasukan</p>
            <p class="mt-1 text-xl font-semibold text-masjid-800">Rp {{ number_format($totals['income'], 0, ',', '.') }}</p>
        </x-public.card>
        <x-public.card>
            <p class="text-sm text-masjid-600">Total pengeluaran</p>
            <p class="mt-1 text-xl font-semibold text-masjid-800">Rp {{ number_format($totals['expense'], 0, ',', '.') }}</p>
        </x-public.card>
        <x-public.card tone="dark">
            <p class="text-sm text-masjid-100">Saldo</p>
            <p class="mt-1 text-xl font-semibold">Rp {{ number_format($totals['balance'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-masjid-200">{{ $totals['count'] }} transaksi tercatat</p>
        </x-public.card>
    </div>

    <x-public.card class="mb-8">
        <form method="get" class="grid gap-4 lg:grid-cols-5">
            <div>
                <label for="dari" class="block text-sm font-medium text-masjid-800">Dari tanggal</label>
                <input id="dari" type="date" name="dari" value="{{ $filters['dari'] }}"
                       class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
            </div>
            <div>
                <label for="sampai" class="block text-sm font-medium text-masjid-800">Sampai tanggal</label>
                <input id="sampai" type="date" name="sampai" value="{{ $filters['sampai'] }}"
                       class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
            </div>
            <div>
                <label for="jenis" class="block text-sm font-medium text-masjid-800">Jenis</label>
                <select id="jenis" name="jenis" class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    <option value="">Semua</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected($filters['jenis'] === $type->value)>{{ $type->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kategori" class="block text-sm font-medium text-masjid-800">Kategori</label>
                <select id="kategori" name="kategori[]" multiple size="1"
                        class="mt-1 w-full rounded-lg border border-masjid-200 px-3 py-2 text-sm shadow-sm focus:ring-2 focus:ring-masjid-400">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(in_array((string) $category->id, array_map('strval', $filters['kategori']), true))>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-masjid-500">Tahan Ctrl untuk memilih lebih dari satu.</p>
            </div>
            <div class="flex items-start gap-2 pt-6">
                <button type="submit" class="rounded-lg bg-masjid-600 px-4 py-2 text-sm font-semibold text-white hover:bg-masjid-700">Terapkan</button>
                <a href="{{ route('laporan-keuangan') }}" class="rounded-lg border border-masjid-200 px-4 py-2 text-sm text-masjid-700 hover:bg-masjid-50">Reset</a>
            </div>
        </form>

        <div class="mt-4 border-t border-masjid-100 pt-4">
            <a href="{{ route('laporan-keuangan.unduh', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-emas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emas-600">
                Unduh laporan (PDF)
            </a>
            <span class="ms-2 text-xs text-masjid-500">Hasil unduhan mengikuti filter yang sedang aktif.</span>
        </div>
    </x-public.card>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section>
            <x-public.section-heading title="Rincian transaksi" />

            <x-public.card class="!p-0">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead class="bg-masjid-50 text-left text-xs uppercase tracking-wide text-masjid-600">
                            <tr>
                                <th scope="col" class="px-5 py-3">Tanggal</th>
                                <th scope="col" class="px-3 py-3">Kategori</th>
                                <th scope="col" class="px-3 py-3">Keterangan</th>
                                <th scope="col" class="px-3 py-3 text-right">Pemasukan</th>
                                <th scope="col" class="px-5 py-3 text-right">Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-masjid-100">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="px-5 py-2.5 whitespace-nowrap text-masjid-700">{{ $transaction->date->translatedFormat('d M Y') }}</td>
                                    <td class="px-3 py-2.5 text-masjid-700">{{ $transaction->category?->name ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-masjid-600">{{ $transaction->description }}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums text-masjid-800">
                                        {{ $transaction->type === \App\Enums\TransactionType::In ? 'Rp '.number_format((float) $transaction->amount, 0, ',', '.') : '' }}
                                    </td>
                                    <td class="px-5 py-2.5 text-right tabular-nums text-masjid-800">
                                        {{ $transaction->type === \App\Enums\TransactionType::Out ? 'Rp '.number_format((float) $transaction->amount, 0, ',', '.') : '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-masjid-500">
                                        Tidak ada transaksi pada filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-public.card>

            <div class="mt-6">{{ $transactions->links() }}</div>
        </section>

        <aside class="space-y-8">
            <section>
                <x-public.section-heading title="Rincian per kategori" />

                @if ($byCategory->isEmpty())
                    <x-public.empty-state message="Belum ada data kategori." />
                @else
                    <x-public.card>
                        <ul class="space-y-3 text-sm">
                            @foreach ($byCategory as $row)
                                <li class="flex items-start justify-between gap-3 border-b border-masjid-100 pb-3 last:border-0 last:pb-0">
                                    <span>
                                        <span class="font-medium text-masjid-800">{{ $row->name }}</span>
                                        <span class="block text-xs text-masjid-500">{{ $row->type->getLabel() }}</span>
                                    </span>
                                    <span class="shrink-0 tabular-nums font-medium text-masjid-700">
                                        Rp {{ number_format($row->total, 0, ',', '.') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </x-public.card>
                @endif
            </section>

            <section>
                <x-public.section-heading title="Ringkasan 12 bulan" />

                <x-public.card class="!p-0">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-masjid-50 text-left text-xs uppercase tracking-wide text-masjid-600">
                                <tr>
                                    <th scope="col" class="px-4 py-2.5">Bulan</th>
                                    <th scope="col" class="px-4 py-2.5 text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-masjid-100">
                                @foreach ($monthlySummary as $row)
                                    <tr>
                                        <th scope="row" class="px-4 py-2 text-left font-normal text-masjid-700">{{ $row->month }}</th>
                                        <td class="px-4 py-2 text-right tabular-nums {{ $row->balance >= 0 ? 'text-masjid-800' : 'text-red-600' }}">
                                            Rp {{ number_format($row->balance, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-public.card>
            </section>
        </aside>
    </div>
@endsection
