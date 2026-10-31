{{-- input teks. name dan id tidak pernah diubah (dibaca controller/JS). nilai: old(name, value); password tidak diisi ulang.
     help = true -> aria-describedby "{id}-help"; galat $errors -> aria-invalid + "{id}-error". --}}
@props(['name', 'id' => null, 'type' => 'text', 'value' => null, 'help' => false])
@php
    $id = $id ?? $name;
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $invalid = isset($errors) && $errors->has($key);
    $current = $type === 'password' ? null : old($key, $value);
    $describedBy = trim(implode(' ', array_filter([$attributes->get('aria-describedby'), $help ? $id.'-help' : null, $invalid ? $id.'-error' : null])));
@endphp
<input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
    @if (! is_null($current) && ! is_array($current)) value="{{ $current }}" @endif
    {{ $attributes->except('aria-describedby')->class('sg-input') }}
    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
    @if ($invalid) aria-invalid="true" data-touched @endif>
