{{-- textarea: perilaku sama seperti x-ui.input (old(), aria-invalid, aria-describedby) --}}
@props(['name', 'id' => null, 'value' => null, 'rows' => 4, 'help' => false])
@php
    $id = $id ?? $name;
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $invalid = isset($errors) && $errors->has($key);
    $describedBy = trim(implode(' ', array_filter([$attributes->get('aria-describedby'), $help ? $id.'-help' : null, $invalid ? $id.'-error' : null])));
@endphp
<textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}"
    {{ $attributes->except('aria-describedby')->class('sg-textarea') }}
    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
    @if ($invalid) aria-invalid="true" data-touched @endif>{{ old($key, $value) }}</textarea>
