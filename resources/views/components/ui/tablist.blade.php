{{-- tab dalam halaman (role=tablist, panah kiri/kanan/home/end). tabs = [id => label]; selected = id awal.
     isi panel dengan <x-ui.tab-panel id="…" prefix="…"> di dalam slot (prefix sama). tanpa JS semua panel terlihat. --}}
@props(['tabs' => [], 'selected' => null, 'label' => 'Bagian', 'prefix' => 'tab'])
@php
    $selected = $selected ?? array_key_first($tabs);
@endphp
<div {{ $attributes }} x-data="sgTabs(@js((string) $selected))">
    <div class="sg-tabs" role="tablist" aria-label="{{ $label }}" x-ref="tablist" @keydown="onKeydown($event)">
        @foreach ($tabs as $tabId => $tabLabel)
            <button type="button" role="tab" class="sg-tabs__tab" data-tab="{{ $tabId }}"
                    id="{{ $prefix }}-{{ $tabId }}-tab" aria-controls="{{ $prefix }}-{{ $tabId }}-panel"
                    aria-selected="{{ (string) $tabId === (string) $selected ? 'true' : 'false' }}"
                    tabindex="{{ (string) $tabId === (string) $selected ? '0' : '-1' }}"
                    :aria-selected="(selected === @js((string) $tabId)).toString()"
                    :tabindex="selected === @js((string) $tabId) ? 0 : -1"
                    @click="select(@js((string) $tabId))">{{ $tabLabel }}</button>
        @endforeach
    </div>
    {{ $slot }}
</div>
