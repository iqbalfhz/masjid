@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-8']) }}>
    <h1 class="text-2xl font-semibold text-masjid-900 sm:text-3xl">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-2 max-w-2xl text-masjid-600">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>
