<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan {{ $setting->name }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #1d3b34; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 18px 0 6px; border-bottom: 1px solid #b9ddcd; padding-bottom: 3px; }
        .meta { color: #5c7a6f; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #cfe0d7; padding: 5px 6px; text-align: left; }
        th { background: #eef6f1; font-size: 10px; text-transform: uppercase; letter-spacing: 0.03em; }
        td.num, th.num { text-align: right; }
        .summary { width: 100%; margin-top: 10px; }
        .summary td { border: 0; padding: 3px 0; }
        .summary .label { color: #5c7a6f; }
        .summary .value { text-align: right; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 9px; color: #7d948a; border-top: 1px solid #cfe0d7; padding-top: 6px; }
    </style>
</head>
<body>
    <h1>Laporan Keuangan {{ $setting->name }}</h1>
    <p class="meta">
        {{ $setting->address }}<br>
        Dicetak {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB
    </p>

    <h2>Periode &amp; Filter</h2>
    <table class="summary">
        <tr>
            <td class="label">Rentang tanggal</td>
            <td class="value">
                {{ $filters['dari'] ? \Illuminate\Support\Carbon::parse($filters['dari'])->translatedFormat('d F Y') : 'Awal pencatatan' }}
                &ndash;
                {{ $filters['sampai'] ? \Illuminate\Support\Carbon::parse($filters['sampai'])->translatedFormat('d F Y') : 'Hari ini' }}
            </td>
        </tr>
        <tr>
            <td class="label">Jenis transaksi</td>
            <td class="value">
                {{ $filters['jenis'] ? \App\Enums\TransactionType::from($filters['jenis'])->getLabel() : 'Semua' }}
            </td>
        </tr>
        <tr>
            <td class="label">Jumlah transaksi</td>
            <td class="value">{{ number_format($totals['count'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <h2>Ringkasan</h2>
    <table class="summary">
        <tr>
            <td class="label">Total pemasukan</td>
            <td class="value">Rp {{ number_format($totals['income'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total pengeluaran</td>
            <td class="value">Rp {{ number_format($totals['expense'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Saldo</td>
            <td class="value">Rp {{ number_format($totals['balance'], 0, ',', '.') }}</td>
        </tr>
    </table>

    @if ($byCategory->isNotEmpty())
        <h2>Rincian per Kategori</h2>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th class="num">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byCategory as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->type->getLabel() }}</td>
                        <td class="num">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Rincian Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th class="num">Pemasukan</th>
                <th class="num">Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date->translatedFormat('d M Y') }}</td>
                    <td>{{ $transaction->category?->name ?? '-' }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td class="num">
                        {{ $transaction->type === \App\Enums\TransactionType::In ? number_format((float) $transaction->amount, 0, ',', '.') : '' }}
                    </td>
                    <td class="num">
                        {{ $transaction->type === \App\Enums\TransactionType::Out ? number_format((float) $transaction->amount, 0, ',', '.') : '' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">
        Dokumen ini dihasilkan otomatis oleh Sistem Informasi {{ $setting->name }}.
        Untuk pertanyaan mengenai laporan, hubungi bendahara DKM.
    </p>
</body>
</html>
