{{-- dropdown umum (pola disclosure): tombol pemicu + panel overlay. esc/klik di luar/fokus keluar menutup,
     panah atas/bawah berpindah antar .sg-menu__item. isi dengan x-ui.menu-item. label = teks tombol;
     slot trigger untuk isi pemicu sendiri (beri aria-label bila hanya ikon). --}}
@props(['id', 'label' => null, 'align' => 'end', 'triggerClass' => 'sg-btn sg-btn--secondary'])
<div {{ $attributes->class('sg-dropdown') }} x-data="sgDisclosure" @keydown.escape.stop="close(true)" @click.outside="close()"
     @focusout="onFocusOut($event)" @keydown.arrow-down="move($event, 1)" @keydown.arrow-up="move($event, -1)">
    <button type="button" class="{{ $triggerClass }}" x-ref="trigger" @click="toggle()"
            aria-expanded="false" :aria-expanded="open.toString()" aria-controls="{{ $id }}">
        @isset($trigger)
            {{ $trigger }}
        @else
            {{ $label }}<x-icon name="chevron-down" size="sm" class="sg-chevron" />
        @endisset
    </button>
    <div id="{{ $id }}" @class(['sg-overlay sg-menu', 'sg-menu--start' => $align === 'start']) x-ref="panel" x-show="open" x-cloak>
        {{ $slot }}
    </div>
</div>
