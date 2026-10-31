{{-- kelompok radio/checkbox dengan legend; galat $errors[name] ditampilkan di bawah --}}
@props(['legend', 'name' => null, 'required' => false, 'help' => null])
@php
    $key = $name ? trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.') : null;
    $message = ($key && isset($errors)) ? $errors->first($key) : null;
@endphp
<fieldset {{ $attributes->class('sg-fieldset') }} @if ($message) aria-invalid="true" @endif>
    <legend @if ($required) class="sg-label" data-required @endif>{{ $legend }}</legend>
    {{ $slot }}
    @if ($help)<p class="sg-help">{{ $help }}</p>@endif
    @if ($message)<p class="sg-error"><x-icon name="circle-alert" size="sm" />{{ $message }}</p>@endif
</fieldset>
