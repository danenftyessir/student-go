@extends('layouts.app')

@section('title', 'Aplikasi Mahasiswa')

@section('content')
@php
    // phase 19: antrean pelamar + detail lamaran. mulai 1024px keduanya berdampingan (split view);
    // di bawahnya tiap baris membuka halaman detail /applications/{id}. filter lama tetap: search, status, problem_id, sort.
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : '—';
    $statusOptions = collect(\App\Support\StatusVocabulary::values('application'))->map(fn ($entry) => $entry[0])->all();
    $problemOptions = $problems->pluck('title', 'id')->all();
    $filtered = request()->filled('search') || request()->filled('status') || request()->filled('problem_id');
    $activeFilters = (int) request()->filled('status') + (int) request()->filled('problem_id') + (int) (request()->filled('sort') && request('sort') !== 'latest');
    $ids = $applications->pluck('id')->values();
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Aplikasi" description="Tinjau lamaran mahasiswa untuk masalah instansi Anda, lalu terima atau tolak." />

    @if ($stats['total'] > 0)
        <x-ui.summary label="Ringkasan lamaran" class="sg-inst-page__summary" :items="[
            ['value' => $stats['total'], 'label' => 'lamaran'],
            ['value' => $stats['pending'] ?: null, 'label' => 'menunggu'],
            ['value' => $stats['under_review'] ?: null, 'label' => 'ditinjau'],
            ['value' => $stats['accepted'] ?: null, 'label' => 'diterima'],
            ['value' => $stats['rejected'] ?: null, 'label' => 'ditolak'],
        ]" />

        <x-ui.filter-bar :action="route('institution.applications.index')" search-label="Cari pelamar" search-placeholder="Cari nama mahasiswa"
                         :reset-href="route('institution.applications.index')" :active="$activeFilters" id="filter-aplikasi" class="sg-inst-page__filters">
            <x-ui.field label="Status" for="status">
                <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" />
            </x-ui.field>
            <x-ui.field label="Masalah" for="problem_id">
                <x-ui.select name="problem_id" :options="$problemOptions" :selected="request('problem_id')" placeholder="Semua masalah" />
            </x-ui.field>
            <x-ui.field label="Urutkan" for="sort">
                <x-ui.select name="sort" :options="['latest' => 'Terbaru', 'oldest' => 'Terlama', 'name' => 'Nama (A–Z)']" :selected="request('sort', 'latest')" />
            </x-ui.field>
        </x-ui.filter-bar>
    @endif

    @if ($applications->count() > 0)
        <div class="sg-inst-review" x-data="sgReviewQueue({ ids: @js($ids), initial: @js($ids->first()) })">
            <div class="sg-inst-review__queue">
                <ul class="sg-inst-queue" aria-label="Daftar pelamar" @keydown="onKey($event)">
                    @foreach ($applications as $application)
                        <li>
                            <a href="{{ route('institution.applications.show', $application->id) }}" class="sg-inst-queue__item" data-id="{{ $application->id }}"
                               @click="pick($event, {{ $application->id }})" :aria-current="wide && selected === {{ $application->id }} ? 'true' : null">
                                <x-ui.avatar :name="$application->student?->display_name" size="sm" decorative />
                                <span class="sg-inst-queue__body">
                                    <span class="sg-inst-queue__top">
                                        <span class="sg-inst-queue__name">{{ $application->student?->display_name }}</span>
                                        <x-ui.status domain="application" :value="$application->status" />
                                    </span>
                                    <span class="sg-inst-queue__meta">{{ collect([$application->student?->university?->name, $application->student?->major])->filter()->implode(' · ') }}</span>
                                    @unless (request()->filled('problem_id'))
                                        <span class="sg-inst-queue__meta">{{ $application->problem?->title }}</span>
                                    @endunless
                                    <span class="sg-inst-queue__date">Dikirim {{ $tanggal($application->applied_at ?? $application->created_at) }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="sg-inst-queue__hint" x-show="wide" x-cloak>Panah atas/bawah memindah pilihan pelamar.</p>
            </div>

            <div class="sg-inst-review__detail">
                <p class="sr-only" aria-live="polite" x-text="announce"></p>
                @foreach ($applications as $application)
                    <section id="lamaran-{{ $application->id }}" x-show="selected === {{ $application->id }}" @unless ($loop->first) x-cloak @endunless>
                        @include('institution.applications.components.detail', ['application' => $application, 'context' => 'panel'])
                    </section>
                @endforeach
            </div>
        </div>

        <x-ui.pagination :paginator="$applications" label="Halaman daftar pelamar" class="sg-inst-page__pagination" />
    @elseif ($filtered)
        <x-ui.empty-state illustration="no-search-results" title="Tidak ada lamaran yang cocok"
                          description="Ubah kata kunci, status atau masalah, atau tampilkan semua lamaran.">
            <x-slot:action>
                <x-ui.button variant="secondary" :href="route('institution.applications.index')">Tampilkan semua lamaran</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran"
                          description="Lamaran mahasiswa akan muncul di sini setelah masalah instansi Anda dibuka.">
            <x-slot:action>
                <x-ui.button variant="secondary" :href="route('institution.problems.index')">Lihat masalah</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @endif
</div>
@endsection

@push('scripts')
<script>
// phase 19: antrean pelamar. mulai 1024px klik/enter menampilkan detail di kolom kanan, panah atas/bawah (home/end)
// memindah pilihan (D-26, disetujui user untuk phase 19). di bawah 1024px tautan membuka halaman detail seperti biasa.
document.addEventListener('alpine:init', () => {
    Alpine.data('sgReviewQueue', (cfg = {}) => ({
        ids: cfg.ids || [],
        selected: cfg.initial ?? null,
        wide: false,
        announce: '',
        init() {
            // simpan akar komponen: di handler event, $el adalah elemen pemicu
            this.root = this.$el;
            const mq = window.matchMedia('(min-width: 1024px)');
            this.wide = mq.matches;
            mq.addEventListener('change', (e) => { this.wide = e.matches; });
            const fromHash = Number((location.hash.match(/^#lamaran-(\d+)$/) || [])[1]);
            if (this.ids.includes(fromHash)) this.selected = fromHash;
        },
        link(id) { return this.root.querySelector(`.sg-inst-queue__item[data-id="${id}"]`); },
        select(id) {
            this.selected = id;
            history.replaceState(null, '', `${location.pathname}${location.search}#lamaran-${id}`);
            const name = this.link(id)?.querySelector('.sg-inst-queue__name')?.textContent.trim() || '';
            this.announce = `Detail lamaran ${name} ditampilkan.`;
        },
        pick(event, id) {
            if (!this.wide) return;
            event.preventDefault();
            this.select(id);
            // enter/klik: fokus ke nama di panel agar isi lamaran dan tombol keputusan berikutnya dalam urutan tab
            this.$nextTick(() => document.getElementById(`lamaran-${id}-nama`)?.focus({ preventScroll: true }));
        },
        onKey(event) {
            if (!this.wide || !['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const last = this.ids.length - 1;
            const current = Math.max(0, this.ids.indexOf(this.selected));
            const index = { ArrowDown: Math.min(last, current + 1), ArrowUp: Math.max(0, current - 1), Home: 0, End: last }[event.key];
            this.select(this.ids[index]);
            this.link(this.ids[index])?.focus();
        },
    }));
});
</script>
@endpush
