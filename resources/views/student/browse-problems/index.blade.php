{{-- resources/views/student/browse-problems/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Telusuri Masalah')

{{-- phase 8: direktori masalah KKN. satu form GET (nama parameter tetap: search, province_id, regency_id, sdg_categories[],
     difficulty, duration, is_urgent, is_featured, sort, page, view), daftar sebagai tampilan default, galeri dan peta leaflet
     sebagai alternatif. urutan dan hitungan dari controller (phase 1, D-05); tanpa hero foto. --}}

@php
    $indexUrl = route('student.browse-problems.index');
    $query = request()->except('page');

    $difficulties = ['beginner' => [1, 'Pemula'], 'intermediate' => [2, 'Menengah'], 'advanced' => [3, 'Lanjut']];
    $durations = ['1-2' => '1–2 bulan', '3-4' => '3–4 bulan', '5-6' => '5–6 bulan', '6+' => 'Lebih dari 6 bulan'];
    $sorts = ['latest' => 'Terbaru', 'deadline' => 'Tenggat terdekat', 'popular' => 'Paling banyak dilihat', 'oldest' => 'Terlama'];
    $currentSort = array_key_exists(request('sort'), $sorts) ? request('sort') : 'latest';
    $selectedSdgs = array_values(array_filter(array_map('intval', (array) request('sdg_categories', []))));

    // url tanpa satu filter (chip "hapus"); halaman selalu kembali ke 1
    $without = function (array $keys, ?int $sdg = null) use ($query, $indexUrl) {
        $q = $query;
        foreach ($keys as $key) {
            unset($q[$key]);
        }
        if ($sdg !== null) {
            $q['sdg_categories'] = array_values(array_filter((array) ($query['sdg_categories'] ?? []), fn ($v) => (int) $v !== $sdg));
            if (! $q['sdg_categories']) {
                unset($q['sdg_categories']);
            }
        }
        return $indexUrl . ($q ? '?' . http_build_query($q) : '');
    };

    // filter aktif sebagai chip yang bisa dihapus (sort dan view bukan filter)
    $activeFilters = [];
    if (filled(request('search'))) {
        $activeFilters[] = ['label' => 'Kata kunci: “' . request('search') . '”', 'url' => $without(['search'])];
    }
    if (filled(request('province_id'))) {
        $province = $provinces->firstWhere('id', (int) request('province_id'));
        $activeFilters[] = ['label' => 'Provinsi: ' . ($province->name ?? request('province_id')), 'url' => $without(['province_id', 'regency_id'])];
    }
    if (filled(request('regency_id'))) {
        $regency = collect($regencies)->firstWhere('id', (int) request('regency_id'));
        $activeFilters[] = ['label' => 'Kab/Kota: ' . ($regency->name ?? request('regency_id')), 'url' => $without(['regency_id'])];
    }
    foreach ($selectedSdgs as $sdg) {
        $activeFilters[] = ['label' => 'SDG ' . $sdg . ' · ' . \App\Http\Controllers\HomeController::sdgName($sdg), 'url' => $without([], $sdg)];
    }
    if (isset($difficulties[request('difficulty')])) {
        $activeFilters[] = ['label' => 'Kesulitan: ' . $difficulties[request('difficulty')][1], 'url' => $without(['difficulty'])];
    }
    if (isset($durations[request('duration')])) {
        $activeFilters[] = ['label' => 'Durasi: ' . $durations[request('duration')], 'url' => $without(['duration'])];
    }
    if (request('is_urgent') == '1') {
        $activeFilters[] = ['label' => 'Mendesak', 'url' => $without(['is_urgent'])];
    }
    if (request('is_featured') == '1') {
        $activeFilters[] = ['label' => 'Unggulan', 'url' => $without(['is_featured'])];
    }
    $clearUrl = $indexUrl . (request('view') ? '?view=' . urlencode(request('view')) : '');
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

@push('styles')
{{-- leaflet (AD-08): tetap dari unpkg seperti sebelumnya --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
@endpush

@section('content')
<div class="sg-container sg-browse">
    <x-ui.page-header
        title="Telusuri Masalah"
        description="Masalah KKN yang sedang dibuka instansi. Bandingkan lokasi, kuota dan tenggat sebelum melamar." />

    <div class="sg-browse__layout" x-data="sgFilterBar" @keydown.escape="close()">
        @include('student.browse-problems.components.filter-sidebar')

        <div class="sg-browse__main">
            {{-- satu kolom cari + urutan; keduanya milik form filter (atribut form) --}}
            <div class="sg-browse__toolbar">
                <div class="sg-browse__search">
                    <x-ui.search name="search" label="Cari masalah" placeholder="Cari judul atau deskripsi masalah" form="browse-filters" />
                    <button type="submit" form="browse-filters" class="sg-btn sg-btn--secondary">Cari</button>
                </div>
                <div class="sg-browse__sort">
                    <label for="browse-sort" class="sg-label">Urutkan</label>
                    <x-ui.select name="sort" id="browse-sort" form="browse-filters" :options="$sorts" :selected="$currentSort"
                                 x-on:change="$el.form.requestSubmit()" />
                </div>
                <button type="button" class="sg-btn sg-btn--secondary sg-browse__filter-toggle" x-ref="toggle" @click="show()"
                        aria-expanded="false" :aria-expanded="open.toString()" aria-controls="browse-filters">
                    <x-icon name="list-filter" size="sm" />Filter
                    @if (count($activeFilters))<span class="sg-tabs__count">({{ count($activeFilters) }} aktif)</span>@endif
                </button>
            </div>

            @if (count($activeFilters))
                <div class="sg-browse__active" aria-label="Filter aktif" role="group">
                    <ul class="sg-browse__chips">
                        @foreach ($activeFilters as $filter)
                            <li>
                                <a href="{{ $filter['url'] }}" class="sg-browse-chip">
                                    {{ $filter['label'] }}<x-icon name="x" size="sm" /><span class="sr-only"> (hapus filter)</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $clearUrl }}" class="sg-browse__clear">Hapus semua filter</a>
                </div>
            @endif

            <div x-data="sgBrowseView">
                <div class="sg-browse__bar">
                    <p class="sg-browse__count">
                        @if (count($activeFilters))
                            <b>{{ $fmt($totalProblems) }}</b> dari {{ $fmt($totalOpenProblems) }} masalah dibuka sesuai filter
                        @else
                            <b>{{ $fmt($totalOpenProblems) }}</b> masalah dibuka
                        @endif
                    </p>
                    @if ($totalProblems > 0)
                        <div class="sg-browse-views" role="tablist" aria-label="Tampilan" x-ref="tablist" @keydown="onKeydown($event)">
                            @foreach (['list' => ['Daftar', 'layout-list'], 'grid' => ['Galeri', 'layout-grid'], 'map' => ['Peta', 'map']] as $viewId => [$viewLabel, $viewIcon])
                                <button type="button" role="tab" class="sg-browse-views__tab" data-view="{{ $viewId }}"
                                        id="browse-{{ $viewId }}-tab" aria-controls="browse-{{ $viewId }}-panel"
                                        aria-selected="{{ $viewId === 'list' ? 'true' : 'false' }}" tabindex="{{ $viewId === 'list' ? '0' : '-1' }}"
                                        :aria-selected="(view === '{{ $viewId }}').toString()" :tabindex="view === '{{ $viewId }}' ? 0 : -1"
                                        @click="select('{{ $viewId }}')">
                                    <x-icon :name="$viewIcon" size="sm" />{{ $viewLabel }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($totalProblems === 0)
                    {{-- tidak ada hasil untuk pencarian/filter ini (S25) --}}
                    <x-ui.empty-state
                        :title="count($activeFilters) ? 'Tidak ada masalah yang cocok' : 'Belum ada masalah yang dibuka'"
                        :description="count($activeFilters) ? 'Tidak ada masalah dibuka yang cocok dengan pencarian dan filter Anda. Hapus salah satu filter di atas, atau mulai lagi dari semua masalah.' : 'Instansi belum membuka masalah KKN. Coba lagi nanti.'"
                        illustration="no-search-results"
                        class="sg-browse__empty">
                        @if (count($activeFilters))
                            <x-slot:action>
                                <x-ui.button variant="secondary" :href="$clearUrl">Hapus semua filter</x-ui.button>
                            </x-slot:action>
                        @endif
                    </x-ui.empty-state>
                @else
                    <h2 class="sr-only">Hasil</h2>

                    <div role="tabpanel" id="browse-list-panel" aria-labelledby="browse-list-tab" x-show="view === 'list'">
                        @if ($problems->isEmpty())
                            @include('student.browse-problems.components.page-empty')
                        @else
                            @include('student.browse-problems.components.problem-table')
                        @endif
                    </div>

                    <div role="tabpanel" id="browse-grid-panel" aria-labelledby="browse-grid-tab" x-show="view === 'grid'" x-cloak>
                        @if ($problems->isEmpty())
                            @include('student.browse-problems.components.page-empty')
                        @else
                            @include('student.browse-problems.components.problems-grid')
                        @endif
                    </div>

                    <div role="tabpanel" id="browse-map-panel" aria-labelledby="browse-map-tab" x-show="view === 'map'" x-cloak>
                        @include('student.browse-problems.components.map-view', ['mapProblems' => $allProblems])
                    </div>

                    {{-- peta memuat semua hasil, jadi paginasi hanya untuk daftar dan galeri --}}
                    <div x-show="view !== 'map'">
                        <x-ui.pagination :paginator="$problems" label="Halaman daftar masalah" class="sg-browse__pager" />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- leaflet + cluster (AD-08) sebelum alpine menginisialisasi peta --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    // tampilan daftar / galeri / peta. state di ?view= (nilai lama 'grid' = galeri) dan localStorage; default daftar
    Alpine.data('sgBrowseView', () => ({
        view: 'list',
        views: ['list', 'grid', 'map'],
        init() {
            let saved = null;
            try { saved = localStorage.getItem('sgBrowseView'); } catch (e) {}
            const fromUrl = new URLSearchParams(window.location.search).get('view');
            this.select([fromUrl, saved].find((v) => this.views.includes(v)) || 'list');
        },
        select(view) {
            this.view = view;
            try { localStorage.setItem('sgBrowseView', view); } catch (e) {}
            const keep = (href) => {
                const url = new URL(href, window.location.href);
                if (view === 'list') url.searchParams.delete('view'); else url.searchParams.set('view', view);
                return url.toString();
            };
            window.history.replaceState(null, '', keep(window.location.href));
            // tautan halaman ini (paginasi, chip, urut tenggat) membawa tampilan yang sama
            document.querySelectorAll('.sg-browse a[href*="/student/browse-problems"]').forEach((a) => {
                if (new URL(a.href).pathname.replace(/\/$/, '') === '/student/browse-problems') a.href = keep(a.href);
            });
            const input = document.getElementById('browse-view-input');
            if (input) { input.value = view; input.disabled = view === 'list'; }
            if (view === 'map') this.$nextTick(() => window.dispatchEvent(new CustomEvent('mapViewActivated')));
        },
        onKeydown(e) {
            const tabs = [...this.$refs.tablist.querySelectorAll('[role="tab"]')];
            const i = tabs.indexOf(document.activeElement);
            const keys = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
            if (i === -1 || !(e.key in keys)) return;
            e.preventDefault();
            const next = tabs[(keys[e.key] + tabs.length) % tabs.length];
            next.focus();
            this.select(next.dataset.view);
        },
    }));

    // provinsi -> kabupaten/kota lewat route yang sudah ada (student.problems.get-regencies); request lama dibatalkan
    Alpine.data('sgBrowseRegions', (cfg) => ({
        provinceId: cfg.provinceId,
        regencyId: cfg.regencyId,
        regencies: cfg.regencies,
        loading: false,
        message: '',
        controller: null,
        seq: 0,
        desktop() { return window.matchMedia('(min-width: 1024px)').matches; },
        async onProvince() {
            this.regencyId = '';
            this.regencies = [];
            // di desktop form langsung dikirim (auto-apply) dan server memuat daftar kabupaten/kota
            if (!this.provinceId || this.desktop()) return;
            this.controller?.abort();
            this.controller = new AbortController();
            const seq = ++this.seq;
            this.loading = true;
            this.message = 'Memuat kabupaten/kota…';
            try {
                const res = await fetch(cfg.url + '?province_id=' + encodeURIComponent(this.provinceId),
                    { headers: { Accept: 'application/json' }, signal: this.controller.signal });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                if (seq !== this.seq) return;
                this.regencies = Array.isArray(data) ? data : [];
                this.message = this.regencies.length + ' kabupaten/kota dimuat';
            } catch (e) {
                if (e.name === 'AbortError') return;
                this.message = 'Daftar kabupaten/kota gagal dimuat. Coba pilih provinsi lagi.';
            } finally {
                if (seq === this.seq) this.loading = false;
            }
        },
    }));
});
</script>
@endpush
