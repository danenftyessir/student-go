@extends('layouts.app')

@section('title', $company->name . ' — Profil Perusahaan')

{{-- phase 29 paket E: profil perusahaan publik = pola organisasi .sg-org (profil instansi publik phase 5, profil perusahaan
     phase 25). tanpa foto hero lama, logo atau inisial, lowongan sebagai daftar bergaris.
     halaman ini dapat dibuka tamu: hanya data yang sudah tampil di versi lama atau di profil instansi publik
     (email perusahaan tanpa jatuh balik ke email akun; alamat, kode pos dan tahun berdiri tidak ditampilkan). --}}

@php
    // label jumlah karyawan sama dengan profil perusahaan phase 25 (D-06)
    $sizeLabels = ['1-10' => '1–10 karyawan', '11-50' => '11–50 karyawan', '51-200' => '51–200 karyawan', '201-500' => '201–500 karyawan', '501-1000' => '501–1.000 karyawan', '1000+' => 'Lebih dari 1.000 karyawan'];
    $size = $company->company_size ?? $company->employee_count;
    $sizeLabel = $size ? ($sizeLabels[$size] ?? $size . ' karyawan') : null;
    $industry = $company->industry === 'Retail' ? 'Ritel' : $company->industry;
    $place = collect([$company->city, $company->province?->name])->filter()->unique()->implode(', ');
    // hanya tautan http(s) yang dijadikan link
    $link = fn ($url) => filled($url) && preg_match('#^https?://#i', $url) ? $url : null;
    $website = $link($company->website);
    $socials = array_filter([
        'LinkedIn' => $company->linkedin,
        'Twitter/X' => $company->twitter,
        'Facebook' => $company->facebook,
        'Instagram' => $company->instagram,
    ], fn ($url) => filled($url));
    $jobTypeLabels = \App\Http\Controllers\Company\JobPostingController::JOB_TYPE_LABELS;
    $jobType = fn ($type) => collect($jobTypeLabels)->first(fn ($label, $key) => strcasecmp($key, (string) $type) === 0) ?? $type;
    $isStudent = auth()->check() && auth()->user()->user_type === 'student';
@endphp

@section('content')
<div class="sg-container sg-org sg-co-public">
    <header class="sg-org__head">
        <x-ui.avatar :src="$company->logo ? $company->logo_url : null" :name="$company->name" size="xl" square decorative class="sg-org__logo" />
        <div class="sg-org__id">
            <p class="sg-org__kicker">Profil perusahaan</p>
            <h1 class="sg-org__name">{{ $company->name }}</h1>
            @if (filled($company->tagline))
                <p class="sg-co-profile__tagline">{{ $company->tagline }}</p>
            @endif
            <p class="sg-org__meta">
                @if ($industry)<span>{{ $industry }}</span>@endif
                @if ($place)<span class="sg-org__place"><x-icon name="map-pin" size="sm" />{{ $place }}</span>@endif
                @if ($company->is_verified)<x-ui.status domain="verification" value="verified" />@endif
            </p>
            {{-- angka dari query di controller; nilai nol tidak ditampilkan (guardrail 2) --}}
            <x-ui.summary class="sg-org__summary" label="Ringkasan perusahaan" :items="[
                ['value' => ($stats['active_jobs'] ?? 0) ?: null, 'label' => 'lowongan dibuka'],
                ['value' => ($stats['total_jobs'] ?? 0) ?: null, 'label' => 'lowongan'],
            ]" />
        </div>
    </header>

    <div class="sg-org__grid">
        <div class="sg-org__main">
            @if (filled($company->description))
                <section class="sg-org__section" aria-labelledby="org-tentang">
                    <h2 id="org-tentang" class="sg-org__h2">Tentang perusahaan</h2>
                    <p class="sg-org__about sg-co-profile__about">{{ $company->description }}</p>
                </section>
            @endif

            <section class="sg-org__section" aria-labelledby="org-lowongan">
                <h2 id="org-lowongan" class="sg-org__h2">Lowongan yang dibuka</h2>

                @if ($activeJobs->isEmpty())
                    <x-ui.empty-state class="sg-org__empty" :heading-level="3" title="Belum ada lowongan yang dibuka"
                                      description="Lowongan dari perusahaan ini akan tampil di sini." />
                @else
                    <ul class="sg-org__problems" aria-label="Lowongan dari {{ $company->name }}">
                        @foreach ($activeJobs as $job)
                            <li class="sg-org-problem">
                                <div class="sg-org-problem__main">
                                    <h3 class="sg-org-problem__title">
                                        <a href="{{ route('student.jobs.show', $job->id) }}">{{ $job->title }}</a>
                                    </h3>
                                    @if (filled($job->location))
                                        <p class="sg-org-problem__place"><x-icon name="map-pin" size="sm" />{{ $job->location }}</p>
                                    @endif
                                </div>
                                <dl class="sg-org-problem__facts">
                                    @if (filled($job->job_type))
                                        <div><dt>Tipe</dt><dd>{{ $jobType($job->job_type) }}</dd></div>
                                    @endif
                                    @if ($job->published_at)
                                        <div><dt>Dibuka</dt><dd>{{ $job->published_at->locale('id')->translatedFormat('j M Y') }}</dd></div>
                                    @endif
                                </dl>
                            </li>
                        @endforeach
                    </ul>
                    @if ($activeJobs->count() >= 5)
                        <p class="sg-co-public__more">
                            <a href="{{ route('student.jobs.index', ['company' => $company->id]) }}" class="sg-org__link">Lihat semua lowongan<x-icon name="arrow-right" size="sm" /></a>
                        </p>
                    @endif
                    @unless ($isStudent)
                        <p class="sg-org__note"><x-icon name="info" size="sm" />Detail lowongan dan lamaran tersedia setelah masuk sebagai mahasiswa.</p>
                    @endunless
                @endif
            </section>
        </div>

        <aside class="sg-org__aside" aria-labelledby="org-data">
            <h2 id="org-data" class="sg-org__h2">Data perusahaan</h2>
            <x-ui.dl :items="[
                'Industri' => $industry,
                'Jumlah karyawan' => $sizeLabel,
                'Kabupaten/kota' => $company->city,
                'Provinsi' => $company->province?->name,
                'Lokasi' => $company->location,
                'Terdaftar sejak' => $company->created_at?->locale('id')->translatedFormat('F Y'),
            ]" />

            @if (filled($company->email) || filled($company->phone) || filled($company->website))
                <h2 class="sg-org__h2 sg-co-public__h2">Kontak</h2>
                <ul class="sg-co-public__contact">
                    @if (filled($company->email))
                        <li><x-icon name="mail" size="sm" label="Email" /><a href="mailto:{{ $company->email }}" class="sg-org__link">{{ $company->email }}</a></li>
                    @endif
                    @if (filled($company->phone))
                        <li><x-icon name="phone" size="sm" label="Telepon" /><a href="tel:{{ $company->phone }}" class="sg-org__link">{{ $company->phone }}</a></li>
                    @endif
                    @if ($website)
                        <li><x-icon name="globe" size="sm" label="Situs web" /><a href="{{ $website }}" class="sg-org__link" target="_blank" rel="noopener noreferrer">{{ preg_replace('#^https?://(www\.)?#i', '', rtrim($website, '/')) }}<x-icon name="external-link" size="sm" label="(membuka tab baru)" /></a></li>
                    @elseif (filled($company->website))
                        <li><x-icon name="globe" size="sm" label="Situs web" /><span>{{ $company->website }}</span></li>
                    @endif
                </ul>
            @endif

            @if ($socials)
                <h2 class="sg-org__h2 sg-co-public__h2">Media sosial</h2>
                <ul class="sg-co-public__contact">
                    @foreach ($socials as $network => $url)
                        <li>
                            @if ($link($url))
                                <a href="{{ $url }}" class="sg-org__link" target="_blank" rel="noopener noreferrer">{{ $network }}<x-icon name="external-link" size="sm" label="(membuka tab baru)" /></a>
                            @else
                                <span>{{ $network }}: {{ $url }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>
</div>
@endsection
