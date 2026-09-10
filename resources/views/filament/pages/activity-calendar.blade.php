{{--
    Legenda memakai gaya inline, bukan kelas Tailwind aplikasi: panel Filament
    dibundel dengan stylesheet-nya sendiri sehingga utility kustom seperti
    `bg-masjid-500` tidak tersedia di sini.
--}}
<x-filament-panels::page>
    <div style="display:flex;flex-wrap:wrap;gap:1.25rem;font-size:0.8125rem;align-items:center">
        @foreach ([
            ['warna' => '#0d9488', 'label' => 'Kajian'],
            ['warna' => '#c2760a', 'label' => 'Kegiatan'],
            ['warna' => '#7c3aed', 'label' => 'Peminjaman fasilitas'],
            ['warna' => '#94a3b8', 'label' => 'Belum disetujui'],
        ] as $keterangan)
            <span style="display:inline-flex;align-items:center;gap:0.5rem">
                <span aria-hidden="true"
                      style="width:0.75rem;height:0.75rem;border-radius:9999px;background:{{ $keterangan['warna'] }}"></span>
                {{ $keterangan['label'] }}
            </span>
        @endforeach
    </div>

    <p style="font-size:0.8125rem;opacity:0.7">
        Klik salah satu agenda untuk membuka record aslinya. Pembuatan agenda baru
        dilakukan lewat modul Kajian, Kegiatan, atau Peminjaman Fasilitas agar alur
        approval tetap berjalan.
    </p>

    @livewire(\App\Filament\Widgets\ActivityCalendarWidget::class)
</x-filament-panels::page>
