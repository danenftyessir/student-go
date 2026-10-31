{{-- resources/views/student/browse-problems/detail.blade.php --}}
@extends('layouts.app')

@section('title', $problem->title)

{{-- phase 9: detail masalah sebagai brief. kepala (judul + meta sebaris), rail lamar yang mengikuti status lamaran asli
     (lebih dulu di dom: di layar kecil langsung terlihat; sticky mulai 1024px), kolom baca ber-section (serif, <= 68ch),
     foto hanya bila benar-benar termuat, lokasi proyek dan alamat instansi berlabel, tiga masalah serupa.
     tautan lamar tetap student.applications.create; syarat lamar tetap di ApplicationController@create. --}}

@php
    $institution = $problem->institution;
    $difficulties = ['beginner' => [1, 'Pemula'], 'intermediate' => [2, 'Menengah'], 'advanced' => [3, 'Lanjut']];
    $level = $difficulties[$problem->difficulty_level] ?? null;
    $sdgs = array_values(array_filter(array_map('intval', (array) $problem->sdg_categories)));
    $skills = array_values(array_filter((array) $problem->required_skills));
    $majors = array_values(array_filter((array) $problem->required_majors));
    $deliverables = array_values(array_filter((array) $problem->deliverables));
    $facilities = array_values(array_filter((array) $problem->facilities_provided));
    $fmtDate = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y') : null;

    // lokasi proyek (kolom problem) dipisah dari alamat instansi (kolom institution)
    $projectPlace = implode(', ', array_filter([$problem->village, optional($problem->regency)->name, optional($problem->province)->name]));
    $regionPlace = implode(', ', array_filter([optional($problem->regency)->name, optional($problem->province)->name]));
    // alamat instansi: kolom address + kab/kota + provinsi, tanpa mengulang nama yang sudah tertulis di address
    $institutionAddress = '';
    if ($institution) {
        $address = trim((string) $institution->address);
        $extra = array_filter([optional($institution->regency)->name, optional($institution->province)->name],
            fn ($part) => $part && ! str_contains(mb_strtolower($address), mb_strtolower($part)));
        $institutionAddress = implode(', ', array_filter([$address, ...$extra]));
    }

    // tenggat: predikat yang sama dengan ApplicationController@create (application_deadline < now() = ditutup)
    $deadline = $problem->application_deadline;
    $deadlinePassed = $deadline && $deadline < now();
    $daysLeft = $deadline ? (int) today()->diffInDays($deadline, false) : null;
    $quotaFilled = $problem->required_students > 0 && $acceptedCount >= $problem->required_students;

    // foto: hanya rekaman problem_images; url dibangun di server, tampil hanya setelah termuat (lihat sgBriefGallery)
    $gallery = $problem->images->map(fn ($image) => ['src' => supabase_url($image->image_path), 'caption' => (string) $image->caption])
        ->filter(fn ($image) => $image['src'] !== '')->values();
@endphp

@section('content')
<div class="sg-container sg-brief">
    <header class="sg-page-header sg-brief__head">
        <x-ui.breadcrumb class="sg-page-header__crumbs" :items="[
            'Dashboard' => route('student.dashboard'),
            'Telusuri Masalah' => route('student.browse-problems.index'),
            $problem->title => null,
        ]" />
        <h1>{{ $problem->title }}</h1>
        <ul class="sg-summary sg-brief__meta" aria-label="Ringkasan masalah">
            <li><span class="sr-only">Instansi: </span>{{ $institution->name ?? '—' }}</li>
            @if ($regionPlace)<li><span class="sr-only">Lokasi proyek: </span>{{ $regionPlace }}</li>@endif
            @if (count($sdgs))<li>SDG {{ implode(', ', $sdgs) }}</li>@endif
            <li>Kuota <b>{{ $problem->required_students }}</b> mahasiswa</li>
            @if ($deadline)<li>Tenggat <b>{{ $fmtDate($deadline) }}</b></li>@endif
        </ul>
        <div class="sg-page-header__actions sg-brief__actions" x-data="sgBriefActions({ saved: @js((bool) $isWishlisted), url: @js(route('student.wishlist.toggle', $problem->id)), title: @js($problem->title) })">
            <button type="button" class="sg-btn sg-btn--secondary" @click="toggle()" :aria-pressed="saved.toString()" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}" :disabled="busy">
                <x-icon name="bookmark" size="sm" /><span x-text="saved ? 'Tersimpan' : 'Simpan'">{{ $isWishlisted ? 'Tersimpan' : 'Simpan' }}</span>
            </button>
            <x-ui.icon-button icon="link" label="Bagikan tautan masalah ini" bordered x-on:click="share()" />
        </div>
    </header>

    <div class="sg-brief__tags">
        @if ($problem->is_urgent)<span class="sg-browse-flag"><x-icon name="clock" size="sm" />Mendesak</span>@endif
        @if ($problem->is_featured)<span class="sg-browse-flag"><x-icon name="award" size="sm" />Unggulan</span>@endif
        @if ($level)
            <span class="sg-meter" data-level="{{ $level[0] }}"><span class="sg-meter__bars" aria-hidden="true"><i></i><i></i><i></i></span>Kesulitan: {{ $level[1] }}</span>
        @endif
        @if (count($sdgs))
            <ul class="sg-brief__sdgs" aria-label="Tujuan SDG">
                @foreach ($sdgs as $sdg)
                    <li><x-ui.chip>SDG {{ $sdg }} · {{ \App\Http\Controllers\HomeController::sdgName($sdg) }}</x-ui.chip></li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="sg-brief__layout">
        @include('student.browse-problems.components.brief-rail')

        <article class="sg-brief__main sg-reading" aria-label="Brief masalah">
            <section class="sg-brief-section" aria-labelledby="brief-description">
                <h2 id="brief-description">Deskripsi</h2>
                <div class="sg-prose">{!! nl2br(e($problem->description)) !!}</div>
            </section>

            @if (filled($problem->background))
                <section class="sg-brief-section" aria-labelledby="brief-background">
                    <h2 id="brief-background">Latar belakang</h2>
                    <div class="sg-prose">{!! nl2br(e($problem->background)) !!}</div>
                </section>
            @endif

            @if (filled($problem->objectives))
                <section class="sg-brief-section" aria-labelledby="brief-objectives">
                    <h2 id="brief-objectives">Tujuan</h2>
                    <div class="sg-prose">{!! nl2br(e($problem->objectives)) !!}</div>
                </section>
            @endif

            @if (filled($problem->scope))
                <section class="sg-brief-section" aria-labelledby="brief-scope">
                    <h2 id="brief-scope">Ruang lingkup</h2>
                    <div class="sg-prose">{!! nl2br(e($problem->scope)) !!}</div>
                </section>
            @endif

            @if ($gallery->isNotEmpty())
                @include('student.browse-problems.components.brief-gallery')
            @endif

            @if (count($skills) || count($majors))
                <section class="sg-brief-section" aria-labelledby="brief-requirements">
                    <h2 id="brief-requirements">Persyaratan</h2>
                    <dl class="sg-dl sg-brief__dl">
                        @if (count($skills))
                            <div>
                                <dt>Keahlian yang dibutuhkan</dt>
                                <dd>
                                    <ul class="sg-brief__chips" aria-label="Keahlian">
                                        @foreach ($skills as $skill)<li><x-ui.chip>{{ $skill }}</x-ui.chip></li>@endforeach
                                    </ul>
                                </dd>
                            </div>
                        @endif
                        @if (count($majors))
                            <div><dt>Jurusan</dt><dd>{{ implode(', ', $majors) }}</dd></div>
                        @endif
                    </dl>
                </section>
            @endif

            @if (filled($problem->expected_outcomes) || count($deliverables))
                <section class="sg-brief-section" aria-labelledby="brief-outcomes">
                    <h2 id="brief-outcomes">Hasil dan luaran</h2>
                    <div class="sg-prose">
                        @if (filled($problem->expected_outcomes))<p>{!! nl2br(e($problem->expected_outcomes)) !!}</p>@endif
                        @if (count($deliverables))
                            <ul>@foreach ($deliverables as $item)<li>{{ $item }}</li>@endforeach</ul>
                        @endif
                    </div>
                </section>
            @endif

            @if (count($facilities))
                <section class="sg-brief-section" aria-labelledby="brief-facilities">
                    <h2 id="brief-facilities">Fasilitas dari instansi</h2>
                    <div class="sg-prose">
                        <ul>@foreach ($facilities as $item)<li>{{ $item }}</li>@endforeach</ul>
                    </div>
                </section>
            @endif

            <section class="sg-brief-section" aria-labelledby="brief-place">
                <h2 id="brief-place">Lokasi dan jadwal</h2>
                <dl class="sg-dl sg-brief__dl">
                    <div><dt>Lokasi proyek</dt><dd>{{ $projectPlace ?: '—' }}</dd></div>
                    @if (filled($problem->detailed_location))
                        <div><dt>Detail lokasi proyek</dt><dd>{!! nl2br(e($problem->detailed_location)) !!}</dd></div>
                    @endif
                    @if ($problem->start_date || $problem->end_date)
                        <div><dt>Periode pelaksanaan</dt><dd>{{ $fmtDate($problem->start_date) ?? '—' }} – {{ $fmtDate($problem->end_date) ?? '—' }}</dd></div>
                    @endif
                    @if ($problem->duration_months)
                        <div><dt>Durasi</dt><dd>{{ $problem->duration_months }} bulan</dd></div>
                    @endif
                </dl>
            </section>

            @if ($institution)
                <section class="sg-brief-section" aria-labelledby="brief-institution">
                    <h2 id="brief-institution">Tentang instansi</h2>
                    <div class="sg-brief-org">
                        <x-ui.avatar :src="$institution->logo_path ? $institution->getLogoUrl() : null" :name="$institution->name" square decorative />
                        <div class="sg-brief-org__main">
                            <p class="sg-brief-org__name"><a href="{{ route('institution.public', $institution->id) }}">{{ $institution->name }}</a></p>
                            @if ($institution->type)<p class="sg-brief-org__type">{{ $institution->type }}</p>@endif
                        </div>
                    </div>
                    @if (filled($institution->description))
                        <p class="sg-brief-org__desc">{{ $institution->description }}</p>
                    @endif
                    <dl class="sg-dl sg-brief__dl">
                        <div><dt>Alamat instansi</dt><dd>{{ $institutionAddress ?: '—' }}</dd></div>
                        @if ($institution->phone)<div><dt>Telepon</dt><dd>{{ $institution->phone }}</dd></div>@endif
                        @if ($institution->email)<div><dt>Email</dt><dd>{{ $institution->email }}</dd></div>@endif
                        @if ($institution->website)
                            <div><dt>Situs web</dt><dd><a href="{{ $institution->website }}" target="_blank" rel="noopener noreferrer">{{ $institution->website }}<span class="sr-only"> (tab baru)</span></a></dd></div>
                        @endif
                    </dl>
                    <x-ui.button variant="secondary" :href="route('institution.public', $institution->id)" icon-end="arrow-right" class="sg-brief-org__more">Lihat profil instansi</x-ui.button>
                </section>
            @endif

            @if ($similarProblems->isNotEmpty())
                <section class="sg-brief-section" aria-labelledby="brief-similar">
                    <h2 id="brief-similar">Masalah serupa</h2>
                    <x-ui.list label="Masalah serupa" class="sg-brief-similar">
                        @foreach ($similarProblems as $similar)
                            <x-ui.list-item :title="$similar->title" :href="route('student.browse-problems.show', $similar->id)"
                                :meta="implode(' · ', array_filter([optional($similar->institution)->name, implode(', ', array_filter([optional($similar->regency)->name, optional($similar->province)->name])), $similar->duration_months ? $similar->duration_months . ' bulan' : null]))" />
                        @endforeach
                    </x-ui.list>
                </section>
            @endif
        </article>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // simpan (wishlist, route student.wishlist.toggle yang sama) dan bagikan tautan
    Alpine.data('sgBriefActions', (cfg) => ({
        saved: cfg.saved,
        busy: false,
        async toggle() {
            this.busy = true;
            try {
                const res = await fetch(cfg.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(res.status);
                this.saved = !!data.saved;
                window.showNotification?.(this.saved ? 'Masalah disimpan' : 'Masalah dihapus dari simpanan', 'success');
            } catch (e) {
                window.showNotification?.('Gagal menyimpan. Coba lagi.', 'error');
            } finally {
                this.busy = false;
            }
        },
        async share() {
            const url = window.location.href;
            try {
                if (navigator.share) { await navigator.share({ title: cfg.title, url }); return; }
                await navigator.clipboard.writeText(url);
                window.showNotification?.('Tautan disalin', 'success');
            } catch (e) {
                if (e && e.name === 'AbortError') return;
                window.showNotification?.('Tautan gagal disalin', 'error');
            }
        },
    }));

    // foto masalah: bagian ini baru tampil setelah minimal satu foto termuat; foto yang gagal dibuang beserta keterangannya
    Alpine.data('sgBriefGallery', (images) => ({
        images,
        current: 0,
        ok: [],
        failed: [],
        get visible() { return this.ok.some((i) => !this.failed.includes(i)); },
        get usable() { return this.images.map((_, i) => i).filter((i) => !this.failed.includes(i)); },
        loaded(i) { if (!this.ok.includes(i)) this.ok.push(i); },
        fail(i) {
            if (!this.failed.includes(i)) this.failed.push(i);
            if (i === this.current) {
                const next = this.usable.find((j) => this.ok.includes(j)) ?? this.usable[0];
                if (next !== undefined) this.current = next;
            }
        },
        show(i) { this.current = i; },
    }));
});
</script>
@endpush
