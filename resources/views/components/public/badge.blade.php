@props(['color' => 'masjid'])

@php
    $classes = match ($color) {
        'emas' => 'bg-emas-100 text-emas-800',
        'gray' => 'bg-masjid-100 text-masjid-700',
        default => 'bg-masjid-100 text-masjid-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $slot }}
</span>
