@props(['as' => 'div'])

<{{ $as }} {{ $attributes->merge(['class' => 'rounded-2xl border border-masjid-100 bg-white p-5 shadow-sm']) }}>
    {{ $slot }}
</{{ $as }}>
