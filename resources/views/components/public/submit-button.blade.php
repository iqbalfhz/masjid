<button type="submit" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-lg bg-masjid-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-masjid-700 focus:outline-none focus:ring-2 focus:ring-masjid-400 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
