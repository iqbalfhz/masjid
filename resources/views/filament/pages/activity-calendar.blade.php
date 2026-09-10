<x-filament-panels::page>
    <div class="mb-4 flex flex-wrap gap-4 text-sm">
        <span class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-full" style="background:#0d9488"></span> Kajian
        </span>
        <span class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-full" style="background:#c2760a"></span> Kegiatan
        </span>
        <span class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-full" style="background:#7c3aed"></span> Peminjaman fasilitas
        </span>
        <span class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-full" style="background:#94a3b8"></span> Belum disetujui
        </span>
    </div>

    @livewire(\App\Filament\Widgets\ActivityCalendarWidget::class)
</x-filament-panels::page>
