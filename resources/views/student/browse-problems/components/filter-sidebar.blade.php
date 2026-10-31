{{-- resources/views/student/browse-problems/components/filter-sidebar.blade.php --}}
{{-- panel filter (phase 8): satu form GET dengan nama parameter yang sama seperti sebelumnya.
     >= 1024px: kolom kiri, setiap perubahan langsung diterapkan (tanpa tombol terapkan).
     < 1024px: drawer (sgFilterBar dari komponen bersama: fokus terkunci, esc menutup) dengan tombol "Terapkan" yang lengket.
     kolom cari dan urutan ada di bilah atas daftar dan ikut form ini lewat atribut form="browse-filters". --}}
@php
    $sdgOptions = collect(range(1, 17))->mapWithKeys(fn ($n) => [$n => \App\Http\Controllers\HomeController::sdgName($n)]);
@endphp

<div class="sg-browse-filters__backdrop" :data-open="open ? '' : null" @click="close()" aria-hidden="true"></div>
<form method="GET" action="{{ route('student.browse-problems.index') }}" id="browse-filters" class="sg-browse-filters"
      x-ref="panel" :data-open="open ? '' : null" :role="open ? 'dialog' : null" :aria-modal="open ? 'true' : null"
      aria-labelledby="browse-filters-title"
      x-on:change="if ($event.target.form === $el && window.matchMedia('(min-width: 1024px)').matches) $nextTick(() => $el.requestSubmit())">

    {{-- tampilan aktif ikut terkirim (diisi oleh sgBrowseView) --}}
    <input type="hidden" name="view" id="browse-view-input" value="{{ request('view') }}" @disabled(! filled(request('view')))>

    <div class="sg-browse-filters__head">
        <h2 id="browse-filters-title">Filter</h2>
        @if (count($activeFilters))
            <a href="{{ $clearUrl }}" class="sg-browse__clear sg-browse-filters__reset">Atur ulang</a>
        @endif
        <button type="button" class="sg-icon-btn sg-browse-filters__close" @click="close()" aria-label="Tutup filter"><x-icon name="x" /></button>
    </div>

    <div class="sg-browse-filters__body">
        {{-- lokasi --}}
        <div class="sg-browse-filters__group"
             x-data="sgBrowseRegions({ url: @js(route('student.problems.get-regencies')), provinceId: @js((string) request('province_id', '')), regencyId: @js((string) request('regency_id', '')), regencies: @js(collect($regencies)->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values()) })">
            <x-ui.field label="Provinsi" for="filter-province">
                <x-ui.select name="province_id" id="filter-province" :options="$provinces->pluck('name', 'id')->all()" :selected="request('province_id')"
                             placeholder="Semua provinsi" x-model="provinceId" x-on:change="onProvince()" />
            </x-ui.field>
            <x-ui.field label="Kabupaten/Kota" for="filter-regency" x-show="provinceId" x-cloak>
                <select name="regency_id" id="filter-regency" class="sg-select" x-model="regencyId" :disabled="loading"
                        aria-describedby="filter-regency-status">
                    <option value="">Semua kabupaten/kota</option>
                    <template x-for="regency in regencies" :key="regency.id">
                        <option :value="String(regency.id)" x-text="regency.name" :selected="String(regency.id) === regencyId"></option>
                    </template>
                </select>
                <p class="sg-help" id="filter-regency-status" aria-live="polite" x-text="message"></p>
            </x-ui.field>
        </div>

        {{-- tujuan SDG: beberapa pilihan (OR), daftar bisa diciutkan --}}
        <details class="sg-browse-filters__details" @if (count($selectedSdgs)) open @endif>
            <summary>
                Tujuan SDG
                @if (count($selectedSdgs))<span class="sg-browse-filters__badge">{{ count($selectedSdgs) }} dipilih</span>@endif
                <x-icon name="chevron-down" size="sm" class="sg-chevron" />
            </summary>
            <fieldset class="sg-browse-filters__checks">
                <legend class="sr-only">Tujuan SDG</legend>
                @foreach ($sdgOptions as $value => $label)
                    <x-ui.checkbox name="sdg_categories[]" :value="$value" :label="$value . '. ' . $label"
                                   :checked="in_array($value, $selectedSdgs, true)" id="filter-sdg-{{ $value }}" />
                @endforeach
            </fieldset>
        </details>

        <x-ui.field label="Tingkat kesulitan" for="filter-difficulty">
            <x-ui.select name="difficulty" id="filter-difficulty" :options="collect($difficulties)->map(fn ($d) => $d[1])->all()"
                         :selected="request('difficulty')" placeholder="Semua tingkat" />
        </x-ui.field>

        <x-ui.field label="Durasi" for="filter-duration">
            <x-ui.select name="duration" id="filter-duration" :options="$durations" :selected="request('duration')" placeholder="Semua durasi" />
        </x-ui.field>

        <fieldset class="sg-browse-filters__checks">
            <legend class="sg-label">Tanda</legend>
            <x-ui.checkbox name="is_urgent" value="1" label="Mendesak" :checked="request('is_urgent') == '1'" id="filter-urgent" />
            <x-ui.checkbox name="is_featured" value="1" label="Unggulan" :checked="request('is_featured') == '1'" id="filter-featured" />
        </fieldset>
    </div>

    <div class="sg-browse-filters__foot">
        <button type="submit" class="sg-btn sg-btn--primary">Terapkan</button>
        <a href="{{ $clearUrl }}" class="sg-btn sg-btn--tertiary">Atur ulang</a>
    </div>
</form>
