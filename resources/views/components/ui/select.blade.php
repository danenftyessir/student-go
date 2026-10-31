{{-- select native. options = [nilai => label]; atau isi <option> sendiri lewat slot.
     placeholder = opsi kosong pertama ("Semua …"/"Pilih …"). nilai terpilih: old(name, selected). --}}
@props(['name', 'id' => null, 'options' => [], 'selected' => null, 'placeholder' => null, 'help' => false])
@php
    $id = $id ?? $name;
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $invalid = isset($errors) && $errors->has($key);
    $current = old($key, $selected);
    $describedBy = trim(implode(' ', array_filter([$attributes->get('aria-describedby'), $help ? $id.'-help' : null, $invalid ? $id.'-error' : null])));
@endphp
<select name="{{ $name }}" id="{{ $id }}"
    {{ $attributes->except('aria-describedby')->class('sg-select') }}
    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
    @if ($invalid) aria-invalid="true" data-touched @endif>
    @if (! is_null($placeholder))
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" @selected(! is_null($current) && (string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
    @endforeach
    {{ $slot }}
</select>
