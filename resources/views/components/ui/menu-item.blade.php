{{-- item menu dropdown. href = tautan; method post/delete/... = form dengan @csrf (+ @method) dan tombol submit
     (dipakai untuk keluar: POST route('logout')). tanpa href = tombol biasa. --}}
@props(['href' => null, 'icon' => null, 'method' => null, 'danger' => false, 'current' => false])
@php
    $classes = ['sg-menu__item', 'sg-menu__item--danger' => $danger];
    $verb = strtoupper((string) $method);
@endphp
@if ($href && $verb && $verb !== 'GET')
    <form method="POST" action="{{ $href }}">
        @csrf
        @if ($verb !== 'POST') @method($verb) @endif
        <button type="submit" {{ $attributes->class($classes) }}>
            @if ($icon)<x-icon :name="$icon" size="sm" />@endif
            <span>{{ $slot }}</span>
        </button>
    </form>
@elseif ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }} @if ($current) aria-current="page" @endif>
        @if ($icon)<x-icon :name="$icon" size="sm" />@endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="button" {{ $attributes->class($classes) }}>
        @if ($icon)<x-icon :name="$icon" size="sm" />@endif
        <span>{{ $slot }}</span>
    </button>
@endif
