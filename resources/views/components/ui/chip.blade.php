{{-- chip taksonomi (SDG, kategori, keahlian): sudut kecil bergaris, bukan pill (pill = status). --}}
@props(['icon' => null])
<span {{ $attributes->class('sg-chip') }}>@if ($icon)<x-icon :name="$icon" size="sm" />@endif{{ $slot }}</span>
