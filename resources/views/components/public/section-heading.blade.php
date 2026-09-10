@props(['title', 'href' => null, 'linkLabel' => 'Lihat semua'])

<div class="mb-4 flex items-end justify-between gap-4">
    <h2 class="text-lg font-semibold text-masjid-900">{{ $title }}</h2>
    @if ($href)
        <a href="{{ $href }}" class="shrink-0 text-sm font-medium text-masjid-600 hover:text-masjid-800 hover:underline">
            {{ $linkLabel }} &rarr;
        </a>
    @endif
</div>
