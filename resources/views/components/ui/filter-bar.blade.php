{{-- bilah filter: SATU <form method="GET"> sehingga nama parameter (search, status, major, university_id, …) tetap.
     slot = bidang filter (x-ui.field + x-ui.select, input hidden untuk parameter yang harus dibawa).
     search = nama parameter kolom cari (null = tanpa kolom cari). di bawah 768px bidang filter dibuka sebagai drawer
     dengan tombol "Terapkan" yang lengket. active = jumlah filter aktif (ditampilkan pada tombol Filter). --}}
@props([
    'action' => null,
    'search' => 'search',
    'searchLabel' => 'Cari',
    'searchPlaceholder' => null,
    'resetHref' => null,
    'active' => 0,
    'id' => 'filter',
])
<form method="GET" action="{{ $action ?? url()->current() }}" role="search" {{ $attributes->class('sg-filter-bar') }}
      x-data="sgFilterBar" @keydown.escape="close()">
    @if ($search)
        <x-ui.search :name="$search" :label="$searchLabel" :placeholder="$searchPlaceholder" class="sg-filter-bar__search" />
    @endif

    <button type="button" class="sg-btn sg-btn--secondary sg-filter-bar__toggle" x-ref="toggle" @click="show()"
            aria-expanded="false" :aria-expanded="open.toString()" aria-controls="{{ $id }}-panel">
        <x-icon name="list-filter" size="sm" />Filter
        @if ($active > 0)<span class="sg-tabs__count">({{ $active }} aktif)</span>@endif
    </button>

    <div class="sg-filter-bar__backdrop" :data-open="open ? '' : null" @click="close()" aria-hidden="true"></div>
    <div class="sg-filter-bar__panel" id="{{ $id }}-panel" x-ref="panel" :data-open="open ? '' : null"
         :role="open ? 'dialog' : null" :aria-modal="open ? 'true' : null" aria-labelledby="{{ $id }}-title">
        <div class="sg-filter-bar__drawer-head">
            <h2 id="{{ $id }}-title">Filter</h2>
            <button type="button" class="sg-icon-btn" @click="close()" aria-label="Tutup filter"><x-icon name="x" /></button>
        </div>
        <div class="sg-filter-bar__body">
            {{ $slot }}
        </div>
        <div class="sg-filter-bar__foot">
            <button type="submit" class="sg-btn sg-btn--primary">Terapkan</button>
            @if ($resetHref)
                <a href="{{ $resetHref }}" class="sg-btn sg-btn--tertiary">Atur ulang</a>
            @endif
        </div>
    </div>
</form>
