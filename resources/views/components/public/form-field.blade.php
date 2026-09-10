@props(['name', 'label', 'type' => 'text', 'required' => false, 'help' => null, 'value' => null, 'options' => null, 'rows' => 4])

@php
    $id = 'field-'.$name;
    $hasError = $errors->has($name);
    $control = 'w-full rounded-lg border px-3 py-2 text-sm shadow-sm outline-none transition focus:ring-2 focus:ring-masjid-400 '
        .($hasError ? 'border-red-400 bg-red-50' : 'border-masjid-200 bg-white');
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $id }}" class="block text-sm font-medium text-masjid-800">
        {{ $label }}
        @if ($required)
            <span class="text-red-600" aria-hidden="true">*</span>
            <span class="sr-only">(wajib diisi)</span>
        @endif
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
                  @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                  class="{{ $control }}">{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" @required($required)
                @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                class="{{ $control }}">
            <option value="">— Pilih —</option>
            @foreach ($options ?? [] as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected(old($name, $value) == $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @required($required)
               @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
               class="{{ $control }}">
    @endif

    @if ($help)
        <p class="text-xs text-masjid-500">{{ $help }}</p>
    @endif

    @error($name)
        <p id="{{ $id }}-error" class="text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
