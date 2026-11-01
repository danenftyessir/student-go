{{-- resources/views/institution/profile/public.blade.php --}}
@extends('layouts.app')

@section('title', $institution->name . ' — Profil Instansi')

{{-- phase 5: profil organisasi polos (S24, I11). nama lengkap tanpa pemotongan (J19), ringkasan sebaris
     tanpa angka nol (pengganti lima kotak statistik), masalah sebagai daftar bergaris tanpa gambar
     'No Image' (J12, J18), keadaan kosong yang jujur. data publik sama seperti sebelumnya: email, telepon,
     alamat dan dokumen instansi tidak ditampilkan. --}}

@php
    $place = collect([$institution->regency?->name, $institution->province?->name])->filter()->implode(', ');
    // hanya tautan http(s) yang ditampilkan sebagai situs web
    $website = filled($institution->website) && preg_match('#^https?://#i', $institution->website) ? $institution->website : null;
    $isStudent = auth()->check() && auth()->user()->user_type === 'student';
@endphp

@section('content')
<div class="sg-container sg-org">
    <header class="sg-org__head">
        <x-ui.avatar :src="$institution->logo_path ? $institution->getLogoUrl() : null" :name="$institution->name"
                     size="xl" square decorative class="sg-org__logo" />
        <div class="sg-org__id">
            <p class="sg-org__kicker">Profil instansi</p>
            <h1 class="sg-org__name">{{ $institution->name }}</h1>
            <p class="sg-org__meta">
                @if ($institution->type)<span>{{ $institution->type }}</span>@endif
                @if ($place)<span class="sg-org__place"><x-icon name="map-pin" size="sm" />{{ $place }}</span>@endif
            </p>
            {{-- angka dari query di controller; nilai nol tidak ditampilkan (guardrail 2) --}}
            <x-ui.summary class="sg-org__summary" label="Ringkasan instansi" :items="[
                ['value' => $stats['active_problems'] ?: null, 'label' => 'masalah dibuka'],
                ['value' => $stats['active_projects'] ?: null, 'label' => 'proyek mahasiswa berjalan'],
                ['value' => $stats['completed_projects'] ?: null, 'label' => 'proyek mahasiswa selesai'],
            ]" />
        </div>
    </header>

    <div class="sg-org__grid">
        <div class="sg-org__main">
            @if (filled($institution->description))
                <section class="sg-org__section" aria-labelledby="org-tentang">
                    <h2 id="org-tentang" class="sg-org__h2">Tentang instansi</h2>
                    <p class="sg-org__about">{{ $institution->description }}</p>
                </section>
            @endif

            <section class="sg-org__section" aria-labelledby="org-masalah">
                <h2 id="org-masalah" class="sg-org__h2">Masalah KKN dari instansi ini</h2>

                @if ($institution->problems->isEmpty())
                    <x-ui.empty-state class="sg-org__empty" title="Belum ada masalah yang dipublikasikan"
                                      description="Masalah KKN yang ditulis instansi ini akan tampil di sini.">
                        <x-slot:action>
                            <x-ui.button variant="secondary" :href="route('home')">Lihat masalah yang sedang dibuka</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <ul class="sg-org__problems" aria-label="Masalah KKN dari {{ $institution->name }}">
                        @foreach ($institution->problems as $problem)
                            <li class="sg-org-problem">
                                <div class="sg-org-problem__main">
                                    <h3 class="sg-org-problem__title">
                                        <a href="{{ route('student.browse-problems.show', $problem->id) }}">{{ $problem->title }}</a>
                                    </h3>
                                    @php
                                        $problemPlace = collect([$problem->village, $problem->regency?->name, $problem->province?->name])->filter()->implode(', ');
                                    @endphp
                                    @if ($problemPlace)
                                        <p class="sg-org-problem__place"><x-icon name="map-pin" size="sm" />{{ $problemPlace }}</p>
                                    @endif
                                    @if (count((array) $problem->sdg_categories))
                                        <ul class="sg-org-problem__sdgs" aria-label="Tujuan SDG">
                                            @foreach ((array) $problem->sdg_categories as $sdg)
                                                <li><x-ui.chip>SDG {{ $sdg }} · {{ \App\Http\Controllers\HomeController::sdgName((int) $sdg) }}</x-ui.chip></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <dl class="sg-org-problem__facts">
                                    <div><dt>Status</dt><dd><x-ui.status domain="problem" :value="$problem->status" /></dd></div>
                                    @if ($problem->required_students)
                                        <div><dt>Kebutuhan</dt><dd>{{ $problem->required_students }} mahasiswa</dd></div>
                                    @endif
                                    @if ($problem->duration_months)
                                        <div><dt>Durasi</dt><dd>{{ $problem->duration_months }} bulan</dd></div>
                                    @endif
                                    @if ($problem->application_deadline)
                                        <div><dt>Tenggat</dt><dd>{{ $problem->application_deadline->locale('id')->translatedFormat('j M Y') }}</dd></div>
                                    @endif
                                </dl>
                            </li>
                        @endforeach
                    </ul>
                    @unless ($isStudent)
                        <p class="sg-org__note"><x-icon name="info" size="sm" />Detail masalah dan lamaran tersedia setelah masuk sebagai mahasiswa.</p>
                    @endunless
                @endif
            </section>

            @if ($completedWork->isNotEmpty())
                <section class="sg-org__section" aria-labelledby="org-selesai">
                    <h2 id="org-selesai" class="sg-org__h2">Proyek yang telah selesai</h2>
                    <p class="sg-org__desc">Dikelompokkan per masalah, dengan jumlah mahasiswa yang menyelesaikannya.</p>
                    <ul class="sg-org__done">
                        @foreach ($completedWork as $work)
                            <li>
                                <span class="sg-org__done-title">{{ $work->title }}</span>
                                <span class="sg-org__done-count">{{ $work->students_count }} mahasiswa</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="sg-org__aside" aria-labelledby="org-data">
            <h2 id="org-data" class="sg-org__h2">Data instansi</h2>
            <x-ui.dl :items="[
                'Jenis instansi' => $institution->type,
                'Kabupaten/kota' => $institution->regency?->name,
                'Provinsi' => $institution->province?->name,
                'Terdaftar sejak' => $institution->created_at?->locale('id')->translatedFormat('F Y'),
            ]">
                @if ($website)
                    <div>
                        <dt>Situs web</dt>
                        <dd><a href="{{ $website }}" class="sg-org__link" target="_blank" rel="noopener noreferrer">{{ preg_replace('#^https?://(www\.)?#i', '', rtrim($website, '/')) }}<x-icon name="external-link" size="sm" label="(membuka tab baru)" /></a></dd>
                    </div>
                @endif
            </x-ui.dl>

            @include('institution.profile.components.region-map', ['regions' => $regions])
        </aside>
    </div>
</div>
@endsection
