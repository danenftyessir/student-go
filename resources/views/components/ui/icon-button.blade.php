{{-- tombol ikon saja: label WAJIB (menjadi aria-label). bordered = garis tepi (di atas permukaan putih). --}}
@props(['icon', 'label', 'href' => null, 'type' => 'button', 'bordered' => false, 'size' => null])
@php
    $classes = ['sg-icon-btn', 'sg-icon-btn--bordered' => $bordered];
@endphp
@if ($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" {{ $attributes->class($classes) }}><x-icon :name="$icon" :size="$size" /></a>
@else
    <button type="{{ $type }}" aria-label="{{ $label }}" {{ $attributes->class($classes) }}><x-icon :name="$icon" :size="$size" /></button>
@endif
