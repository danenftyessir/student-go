{{-- resources/views/student/jobs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Lowongan Kerja & Magang')

{{-- phase 15: daftar lowongan dengan pola daftar + filter yang sama seperti telusuri masalah (phase 8).
     parameter tetap: search, category, sdg[], sort, page (+ location, job_type[], salary_min bila ada di url).
     tanpa foto header dan tanpa penghitung pemasaran; jumlah dari controller ($totalJobs, $jobs->total()).
     D-07 (a): KKN tetap kegiatan utama, lowongan pilihan tambahan. --}}

@php
    $indexUrl = route('student.jobs.index');
    $query = request()->except('page');

    $sorts = ['latest' => 'Terbaru', 'oldest' => 'Terlama', 'salary_high' => 'Gaji tertinggi', 'salary_low' => 'Gaji terendah'];
    $currentSort = array_key_exists(request('sort'), $sorts) ? request('sort') : 'latest';
    $sdgOptions = collect(range(1, 17))->mapWithKeys(fn ($n) => [$n => \App\Http\Controllers\HomeController::sdgName($n)]);
    $selectedSdgs = array_values(array_unique(array_filter(array_map('intval', (array) request('sdg', [])), fn ($n) => $n >= 1 && $n <= 17)));
    $selectedTypes = array_values(array_filter((array) request('job_type', []), 'filled'));
    $typeLabels = ['internship' => 'Magang', 'magang' => 'Magang', 'contract' => 'Kontrak', 'full_time' => 'Penuh waktu', 'part_time' => 'Paruh waktu', 'freelance' => 'Lepas'];

    // url tanpa satu filter (chip "hapus"); halaman selalu kembali ke 1
    $without = function (array $keys, ?string $arrayKey = null, $arrayValue = null) use ($query, $indexUrl) {
        $q = $query;
        foreach ($keys as $key) {
            unset($q[$key]);
        }
        if ($arrayKey !== null) {
            $q[$arrayKey] = array_values(array_filter((array) ($query[$arrayKey] ?? []), fn ($v) => (string) $v !== (string) $arrayValue));
            if (! $q[$arrayKey]) {
                unset($q[$arrayKey]);
            }
        }
        return $indexUrl . ($q ? '?' . http_build_query($q) : '');
    };

    // filter aktif sebagai chip yang bisa dihapus (urutan bukan filter)
    $activeFilters = [];
    if (filled(request('search'))) {
        $activeFilters[] = ['label' => 'Kata kunci: “' . request('search') . '”', 'url' => $without(['search'])];
    }
    if (filled(request('category'))) {
        $category = $categories->firstWhere('id', (int) request('category'));
        $activeFilters[] = ['label' => 'Bidang: ' . ($category->name ?? request('category')), 'url' => $without(['category'])];
    }
    foreach ($selectedSdgs as $sdg) {
        $activeFilters[] = ['label' => 'SDG ' . $sdg . ' · ' . $sdgOptions[$sdg], 'url' => $without([], 'sdg', $sdg)];
    }
    if (filled(request('location'))) {
        $activeFilters[] = ['label' => 'Lokasi: ' . request('location'), 'url' => $without(['location'])];
    }
    foreach ($selectedTypes as $type) {
        $activeFilters[] = ['label' => 'Tipe: ' . ($typeLabels[strtolower($type)] ?? $type), 'url' => $without([], 'job_type', $type)];
    }
    if (filled(request('salary_min'))) {
        $activeFilters[] = ['label' => 'Gaji minimal: Rp ' . number_format((float) request('salary_min'), 0, ',', '.'), 'url' => $without(['salary_min'])];
    }
    $clearUrl = $indexUrl;
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $filteredTotal = $jobs->total();
@endphp

@section('content')
<div class="sg-container sg-browse" x-data="jobsPage()">
    <x-ui.page-header
        title="Lowongan Kerja & Magang"
        description="Lowongan dari perusahaan mitra, di luar program KKN. Bandingkan lokasi, tujuan SDG dan tenggat sebelum melamar.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="bookmark" :href="route('student.jobs.saved')">Tersimpan</x-ui.button>
            <x-ui.button variant="secondary" icon="bell" :href="route('student.jobs.alerts')">Notifikasi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="sg-browse__layout" x-data="sgFilterBar" @keydown.escape="close()">
        @include('student.jobs.components.filter-sidebar')

        <div class="sg-browse__main">
            {{-- satu kolom cari + urutan; keduanya milik form filter (atribut form) --}}
            <div class="sg-browse__toolbar">
                <div class="sg-browse__search">
                    <x-ui.search name="search" id="jobs-search" label="Cari lowongan" placeholder="Cari posisi, deskripsi atau divisi" form="jobs-filters" />
                    <button type="submit" form="jobs-filters" class="sg-btn sg-btn--secondary">Cari</button>
                </div>
                <div class="sg-browse__sort">
                    <label for="jobs-sort" class="sg-label">Urutkan</label>
                    <x-ui.select name="sort" id="jobs-sort" form="jobs-filters" :options="$sorts" :selected="$currentSort"
                                 x-on:change="$el.form.requestSubmit()" />
                </div>
                <button type="button" class="sg-btn sg-btn--secondary sg-browse__filter-toggle" x-ref="toggle" @click="show()"
                        aria-expanded="false" :aria-expanded="open.toString()" aria-controls="jobs-filters">
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

            <div class="sg-browse__bar">
                <p class="sg-browse__count">
                    @if (count($activeFilters))
                        <b>{{ $fmt($filteredTotal) }}</b> dari {{ $fmt($totalJobs) }} lowongan dibuka sesuai filter
                    @else
                        <b>{{ $fmt($totalJobs) }}</b> lowongan dibuka
                    @endif
                </p>
            </div>

            @if ($totalJobs === 0)
                {{-- belum ada lowongan aktif sama sekali --}}
                <x-ui.empty-state
                    title="Belum ada lowongan yang dibuka"
                    description="Perusahaan belum membuka lowongan kerja atau magang. Sementara itu, telusuri masalah KKN, atau buat notifikasi agar Anda dikabari saat lowongan baru dibuka."
                    illustration="no-search-results"
                    class="sg-browse__empty">
                    <x-slot:action>
                        <div class="flex flex-wrap justify-center gap-2">
                            <x-ui.button variant="secondary" :href="route('student.browse-problems.index')">Telusuri masalah KKN</x-ui.button>
                            <x-ui.button variant="secondary" :href="route('student.jobs.alerts')">Buat notifikasi lowongan</x-ui.button>
                        </div>
                    </x-slot:action>
                </x-ui.empty-state>
            @elseif ($filteredTotal === 0)
                {{-- tidak ada hasil untuk pencarian/filter ini --}}
                <x-ui.empty-state
                    title="Tidak ada lowongan yang cocok"
                    description="Tidak ada lowongan dibuka yang cocok dengan pencarian dan filter Anda. Hapus salah satu filter di atas, atau mulai lagi dari semua lowongan."
                    illustration="no-search-results"
                    class="sg-browse__empty">
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="$clearUrl">Hapus semua filter</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @elseif ($jobs->isEmpty())
                {{-- nomor halaman di luar jangkauan (?page=99) --}}
                <x-ui.empty-state
                    title="Halaman ini kosong"
                    description="Daftar lowongan tidak sepanjang itu. Kembali ke halaman pertama dengan filter yang sama."
                    class="sg-browse__empty">
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="$jobs->url(1)">Ke halaman 1</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @else
                <h2 class="sr-only">Hasil</h2>
                <x-ui.table caption="Lowongan yang dibuka, halaman {{ $jobs->currentPage() }}" class="sg-browse-table">
                    <x-slot:head>
                        <th scope="col">Posisi</th>
                        <th scope="col">Perusahaan</th>
                        <th scope="col">Lokasi</th>
                        <th scope="col" class="min-w-40">SDG</th>
                        <th scope="col">Tenggat</th>
                    </x-slot:head>
                    @foreach ($jobs as $job)
                        @include('student.jobs.components.job-row', ['job' => $job])
                    @endforeach
                </x-ui.table>

                <x-ui.pagination :paginator="$jobs->withQueryString()" label="Halaman daftar lowongan" class="sg-browse__pager" />
            @endif
        </div>
    </div>

    {{-- bilah banding: muncul setelah memilih lowongan; route student.jobs.compare?ids=… (2–3 lowongan) --}}
    <div x-show="compareList.length > 0" x-cloak role="region" aria-label="Bandingkan lowongan"
         class="fixed inset-x-0 bottom-0 z-40 border-t border-[var(--sg-border-default)] bg-[var(--sg-surface-raised)] shadow-[var(--shadow-overlay)] pb-[env(safe-area-inset-bottom)]">
        <div class="sg-container flex flex-wrap items-center justify-between gap-3 py-3">
            <p class="m-0 text-sm" aria-live="polite">
                <b x-text="compareList.length"></b> dari 3 lowongan dipilih<span x-show="compareList.length < 2"> · pilih minimal 2</span>
            </p>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="sg-btn sg-btn--tertiary" @click="clearCompare()">Kosongkan pilihan</button>
                <button type="button" class="sg-btn sg-btn--primary" @click="goCompare()" :disabled="compareList.length < 2">Bandingkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// simpan/batal simpan lewat POST student/jobs/{id}/toggle-save (respons: action saved|unsaved) dan daftar banding di localStorage
function jobsPage() {
    return {
        savedJobs: @json(array_map('intval', $savedJobIds ?? [])),
        compareList: [],
        busy: [],

        init() {
            try {
                const stored = JSON.parse(localStorage.getItem('compareJobs') || '[]');
                if (Array.isArray(stored)) this.compareList = stored.map(Number).filter(Boolean).slice(0, 3);
            } catch (e) {}
        },

        isSaved(jobId) {
            return this.savedJobs.includes(jobId);
        },

        async toggleSave(jobId) {
            if (this.busy.includes(jobId)) return;
            this.busy.push(jobId);
            try {
                const res = await fetch(`{{ url('student/jobs') }}/${jobId}/toggle-save`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                if (data.action === 'saved') {
                    if (!this.savedJobs.includes(jobId)) this.savedJobs.push(jobId);
                    window.showNotification?.('Lowongan disimpan.', 'success');
                } else {
                    this.savedJobs = this.savedJobs.filter((id) => id !== jobId);
                    window.showNotification?.('Lowongan dihapus dari tersimpan.', 'success');
                }
            } catch (e) {
                window.showNotification?.('Lowongan gagal disimpan. Coba lagi.', 'error');
            } finally {
                this.busy = this.busy.filter((id) => id !== jobId);
            }
        },

        toggleCompare(jobId, input) {
            if (this.compareList.includes(jobId)) {
                this.compareList = this.compareList.filter((id) => id !== jobId);
            } else if (this.compareList.length < 3) {
                this.compareList.push(jobId);
            } else if (input) {
                input.checked = false;
            }
            try { localStorage.setItem('compareJobs', JSON.stringify(this.compareList)); } catch (e) {}
        },

        goCompare() {
            if (this.compareList.length >= 2) {
                window.location.href = `{{ route('student.jobs.compare') }}?ids=${this.compareList.join(',')}`;
            }
        },

        clearCompare() {
            this.compareList = [];
            try { localStorage.removeItem('compareJobs'); } catch (e) {}
        }
    }
}
</script>
@endpush
