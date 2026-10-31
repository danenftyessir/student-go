{{-- pesan sebaris (bukan toast). tone: info | success | warning | danger. danger -> role="alert". --}}
@props(['tone' => 'info', 'title' => null])
@php
    $icon = ['success' => 'circle-check', 'warning' => 'triangle-alert', 'danger' => 'circle-alert'][$tone] ?? 'info';
@endphp
<div {{ $attributes->class('sg-alert') }} @if ($tone !== 'info') data-tone="{{ $tone }}" @endif role="{{ $tone === 'danger' ? 'alert' : 'status' }}">
    <x-icon :name="$icon" />
    <div class="sg-alert__body">
        @if ($title)<p class="sg-alert__title">{{ $title }}</p>@endif
        {{ $slot }}
    </div>
</div>
