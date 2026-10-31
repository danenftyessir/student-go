{{-- bidang formulir: label di atas, kontrol (slot), bantuan, galat validasi server.
     for = id kontrol. name = kunci $errors (default: for). id bantuan/galat: "{for}-help" / "{for}-error"
     (x-ui.input/textarea/select menautkannya otomatis lewat aria-describedby). --}}
@props(['label' => null, 'for' => null, 'name' => null, 'required' => false, 'help' => null, 'error' => null])
@php
    $errorKey = trim(str_replace(['[]', '[', ']'], ['', '.', ''], (string) ($name ?? $for)), '.');
    $message = $error ?? (($errorKey !== '' && isset($errors)) ? $errors->first($errorKey) : null);
@endphp
<div {{ $attributes->class('sg-field') }}>
    @if ($label)
        <label class="sg-label" @if ($for) for="{{ $for }}" @endif @if ($required) data-required @endif>{{ $label }}</label>
    @endif
    {{ $slot }}
    @if ($help)
        <p class="sg-help" id="{{ $for }}-help">{{ $help }}</p>
    @endif
    @if ($message)
        <p class="sg-error" id="{{ $for }}-error"><x-icon name="circle-alert" size="sm" />{{ $message }}</p>
    @endif
</div>
