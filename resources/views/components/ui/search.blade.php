{{-- kolom pencarian dengan ikon. label wajib (tersembunyi secara visual bila hide-label). name default 'search'
     (kontrak query string: search / q dipertahankan oleh pemanggil). nilai: request(name) bila value tidak diberikan. --}}
@props(['name' => 'search', 'id' => null, 'label' => 'Cari', 'value' => null, 'placeholder' => null, 'hideLabel' => true])
@php
    $id = $id ?? 'search-'.$name;
    $current = $value ?? request($name);
@endphp
<div {{ $attributes->only('class')->class('sg-field') }}>
    <label for="{{ $id }}" @class(['sg-label', 'sr-only' => $hideLabel])>{{ $label }}</label>
    <div class="sg-search">
        <x-icon name="search" size="sm" />
        <input type="search" name="{{ $name }}" id="{{ $id }}" class="sg-input" @if (! is_null($current) && ! is_array($current)) value="{{ $current }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif {{ $attributes->except('class') }}>
    </div>
</div>
