{{-- avatar: foto asli bila ada; tanpa foto (atau url pengganti ui-avatars) -> inisial dari nama.
     foto pengguna tidak pernah diganti ilustrasi generik. size: xs|sm|md|lg|xl. square = logo instansi/perusahaan.
     decorative = nama sudah tertulis di sebelahnya (tidak dibacakan dua kali). --}}
@props(['src' => null, 'name' => '', 'size' => 'md', 'square' => false, 'decorative' => false, 'alt' => null])
@php
    $hasPhoto = filled($src) && ! str_contains((string) $src, 'ui-avatars.com');
    $initials = get_initials((string) $name) ?: '?';
    $label = $decorative ? null : ($alt ?? $name);
@endphp
<span {{ $attributes->class([
        'sg-avatar',
        'sg-avatar--'.$size => in_array($size, ['xs', 'sm', 'lg', 'xl'], true),
        'sg-avatar--square' => $square,
    ]) }}
    data-initials="{{ $initials }}" @if ($label) data-label="{{ $label }}" @endif
    @if (! $hasPhoto) @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif @endif>
    @if ($hasPhoto)
        <img src="{{ $src }}" alt="{{ $label ?? '' }}" loading="lazy" decoding="async">
    @else
        {{ $initials }}
    @endif
</span>
