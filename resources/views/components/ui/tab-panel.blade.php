{{-- panel untuk x-ui.tablist (id dan prefix sama dengan tab). active = panel awal (yang lain disembunyikan sampai dipilih). --}}
@props(['id', 'prefix' => 'tab', 'active' => false])
<div {{ $attributes->class('sg-tabpanel') }} role="tabpanel" id="{{ $prefix }}-{{ $id }}-panel"
     aria-labelledby="{{ $prefix }}-{{ $id }}-tab" tabindex="0"
     x-show="selected === @js((string) $id)" @unless ($active) x-cloak @endunless>
    {{ $slot }}
</div>
