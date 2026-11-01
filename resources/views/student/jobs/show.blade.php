{{-- resources/views/student/jobs/show.blade.php --}}
@extends('layouts.app')

@section('title', $job->title . ' - Lowongan Kerja')

@section('content')
@php
    // phase 29 paket C: detail lowongan sebagai rekaman (pola detail lamaran phase 10): kepala halaman, fakta yang membungkus
    // di 360px (C3), bagian isi bergaris, rel kanan mulai 1024px (lamar, perusahaan, lowongan serupa).
    // kontrak tetap: POST student.jobs.apply (cover_letter, resume_url, portfolio_url), DELETE student.jobs.withdraw.
    $company = $job->company;
    // jenis lowongan: pemetaan yang sama dengan daftar lowongan (student/jobs/components/job-row.blade.php)
    $typeLabels = ['internship' => 'Magang', 'magang' => 'Magang', 'contract' => 'Kontrak', 'full_time' => 'Penuh waktu', 'part_time' => 'Paruh waktu', 'freelance' => 'Lepas'];
    $typeLabel = $job->job_type ? ($typeLabels[strtolower($job->job_type)] ?? $job->job_type) : null;
    $tanggal = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y') : null;
    $relatif = fn ($date) => $date ? $date->locale('id')->diffForHumans() : null;
    $skills = collect((array) $job->skills)->filter(fn ($s) => filled($s))->values();
    $sdgs = array_values(array_unique(array_filter(array_map('intval', (array) $job->sdg_alignment), fn ($n) => $n >= 1 && $n <= 17)));
    $sections = array_filter([
        'deskripsi' => ['Deskripsi pekerjaan', $job->description],
        'tanggung-jawab' => ['Tanggung jawab', $job->responsibilities],
        'kualifikasi' => ['Kualifikasi', $job->qualifications],
        'manfaat' => ['Manfaat', $job->benefits],
    ], fn ($part) => filled($part[1]));
@endphp

<div class="sg-container pb-16">
    <x-ui.page-header :title="$job->title" :description="$company->name ?? null"
                      :back="route('student.jobs.index')" back-label="Kembali ke lowongan" class="[&_h1]:wrap-break-word [&_p]:wrap-break-word">
        <x-slot:actions>
            @if ($hasApplied)
                <x-ui.status domain="job_application" :value="$application->status ?? 'new'" />
            @else
                <x-ui.button href="#lamar" icon="send">Lamar posisi ini</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- fakta singkat: membungkus di layar sempit, tidak ada badge yang melebarkan halaman --}}
    <ul class="-mt-2 mb-8 flex list-none flex-wrap gap-x-5 gap-y-2 p-0 text-sm text-gray-600" aria-label="Ringkasan lowongan">
        @if ($typeLabel)
            <li class="flex items-center gap-1.5"><x-icon name="briefcase" size="sm" class="text-gray-500" /><span><span class="sr-only">Jenis: </span>{{ $typeLabel }}</span></li>
        @endif
        @if ($job->location)
            <li class="flex min-w-0 items-center gap-1.5"><x-icon name="map-pin" size="sm" class="flex-none text-gray-500" /><span class="wrap-break-word"><span class="sr-only">Lokasi: </span>{{ $job->location }}</span></li>
        @endif
        @if ($job->department)
            <li class="flex min-w-0 items-center gap-1.5"><x-icon name="building-2" size="sm" class="flex-none text-gray-500" /><span class="wrap-break-word"><span class="sr-only">Departemen: </span>{{ $job->department }}</span></li>
        @endif
        @if ($job->published_at)
            <li class="flex items-center gap-1.5"><x-icon name="clock" size="sm" class="text-gray-500" /><span>Dipublikasikan <time datetime="{{ $job->published_at->toIso8601String() }}">{{ $relatif($job->published_at) }}</time></span></li>
        @endif
    </ul>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:gap-12">
        <div class="min-w-0 divide-y divide-gray-200 [&>section]:py-6 [&>section:first-child]:pt-0">
            @forelse ($sections as $key => [$heading, $text])
                <section aria-labelledby="{{ $key }}">
                    <h2 id="{{ $key }}" class="mt-0 mb-3 text-lg font-semibold text-gray-900">{{ $heading }}</h2>
                    <p class="m-0 max-w-prose text-base text-gray-900 wrap-break-word">{!! nl2br(e($text)) !!}</p>
                </section>
            @empty
                <section aria-labelledby="deskripsi">
                    <h2 id="deskripsi" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Deskripsi pekerjaan</h2>
                    <p class="m-0 text-sm text-gray-600">Perusahaan belum menuliskan deskripsi.</p>
                </section>
            @endforelse

            @if ($skills->isNotEmpty())
                <section aria-labelledby="keahlian">
                    <h2 id="keahlian" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Keahlian yang dibutuhkan</h2>
                    <ul class="m-0 flex list-none flex-wrap gap-2 p-0">
                        @foreach ($skills as $skill)
                            <li><x-ui.chip>{{ $skill }}</x-ui.chip></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (count($sdgs))
                <section aria-labelledby="tujuan-sdg">
                    <h2 id="tujuan-sdg" class="mt-0 mb-3 text-lg font-semibold text-gray-900">Tujuan SDG</h2>
                    <ul class="m-0 flex list-none flex-wrap gap-2 p-0">
                        @foreach ($sdgs as $sdg)
                            <li><span class="sg-chip sg-sdg" data-sdg="{{ $sdg }}"><span class="sg-chip__swatch" aria-hidden="true"></span>SDG {{ $sdg }} · {{ \App\Http\Controllers\HomeController::sdgName($sdg) }}</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="grid min-w-0 content-start gap-4 lg:sticky lg:top-20 lg:self-start" aria-label="Lamaran dan perusahaan">
            <section id="lamar" class="grid scroll-mt-24 gap-4 rounded-lg border border-gray-200 bg-white p-5" aria-labelledby="judul-lamar">
                @if ($hasApplied)
                    <h2 id="judul-lamar" class="m-0 flex items-center gap-2 text-base font-semibold text-gray-900">
                        <x-icon name="circle-check" class="text-success-700" />Anda sudah melamar
                    </h2>
                    <x-ui.dl>
                        <div><dt>Status</dt><dd><x-ui.status domain="job_application" :value="$application->status ?? 'new'" /></dd></div>
                        @if ($application?->applied_at ?? $application?->created_at)
                            <div><dt>Dilamar</dt><dd class="tabular-nums">{{ $tanggal($application->applied_at ?? $application->created_at) }}</dd></div>
                        @endif
                    </x-ui.dl>
                    @if ($application && $application->status === 'new')
                        <form action="{{ route('student.jobs.withdraw', $job->id) }}" method="POST"
                              data-sg-confirm-title="Batalkan lamaran?"
                              data-sg-confirm="Lamaran untuk posisi “{{ $job->title }}” akan ditarik. Tindakan ini tidak dapat dibatalkan."
                              data-sg-confirm-label="Ya, batalkan lamaran">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="danger" type="submit" icon="x" block>Batalkan lamaran</x-ui.button>
                        </form>
                        <p class="m-0 text-xs text-gray-500">Lamaran hanya dapat dibatalkan selama berstatus Menunggu.</p>
                    @endif
                    <a href="{{ route('student.applications.index', ['tab' => 'jobs']) }}" class="inline-flex min-h-6 items-center gap-1 text-sm font-medium text-brand-700 underline-offset-2 hover:underline">
                        Lihat semua lamaran lowongan<x-icon name="arrow-right" size="sm" />
                    </a>
                @else
                    <h2 id="judul-lamar" class="m-0 text-base font-semibold text-gray-900">Lamar posisi ini</h2>
                    <form action="{{ route('student.jobs.apply', $job->id) }}" method="POST" class="grid gap-4">
                        @csrf
                        <x-ui.field label="Surat lamaran (opsional)" for="cover_letter">
                            <x-ui.textarea name="cover_letter" rows="5" placeholder="Ceritakan mengapa Anda cocok untuk posisi ini..." />
                        </x-ui.field>
                        <x-ui.field label="Tautan CV (opsional)" for="resume_url">
                            <x-ui.input type="url" name="resume_url" placeholder="https://..." />
                        </x-ui.field>
                        <x-ui.field label="Tautan portofolio (opsional)" for="portfolio_url">
                            <x-ui.input type="url" name="portfolio_url" placeholder="https://..." />
                        </x-ui.field>
                        <x-ui.button type="submit" block>Kirim lamaran</x-ui.button>
                    </form>
                @endif
            </section>

            <section class="grid gap-4 rounded-lg border border-gray-200 bg-white p-5" aria-labelledby="info-lowongan">
                <h2 id="info-lowongan" class="m-0 text-base font-semibold text-gray-900">Informasi lowongan</h2>
                <x-ui.dl class="grid-cols-2 gap-x-4 lg:grid-cols-1">
                    @if ($typeLabel)<div><dt>Jenis</dt><dd>{{ $typeLabel }}</dd></div>@endif
                    @if ($job->jobCategory)<div><dt>Kategori</dt><dd>{{ $job->jobCategory->name }}</dd></div>@endif
                    @if ($job->published_at)<div><dt>Dipublikasikan</dt><dd class="tabular-nums">{{ $tanggal($job->published_at) }}</dd></div>@endif
                    @if ($job->expires_at)<div><dt>Tenggat</dt><dd class="tabular-nums">{{ $tanggal($job->expires_at) }}</dd></div>@endif
                    <div><dt>Dilihat</dt><dd class="tabular-nums">{{ number_format((int) $job->views_count, 0, ',', '.') }} kali</dd></div>
                </x-ui.dl>
            </section>

            @if ($company)
                <section class="grid gap-3 rounded-lg border border-gray-200 bg-white p-5" aria-labelledby="tentang-perusahaan">
                    <h2 id="tentang-perusahaan" class="m-0 text-base font-semibold text-gray-900">Tentang perusahaan</h2>
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :src="$company->logo_url" :name="$company->name" square decorative />
                        <div class="min-w-0">
                            <p class="m-0 text-sm font-semibold text-gray-900 wrap-break-word">{{ $company->name }}</p>
                            @if ($company->industry)
                                <p class="m-0 text-xs text-gray-500">{{ $company->industry }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($company->description)
                        <p class="m-0 text-sm text-gray-600 wrap-break-word">{{ Str::limit($company->description, 150) }}</p>
                    @endif
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                        <a href="{{ route('company.public', $company->id) }}" class="inline-flex min-h-6 items-center gap-1 font-medium text-brand-700 underline-offset-2 hover:underline">
                            <x-icon name="building-2" size="sm" />Lihat profil perusahaan
                        </a>
                        @if ($company->website)
                            <a href="{{ $company->website }}" target="_blank" rel="noopener" class="inline-flex min-h-6 items-center gap-1 text-brand-700 underline-offset-2 hover:underline">
                                <x-icon name="globe" size="sm" />Kunjungi situs web<span class="sr-only"> (tab baru)</span>
                            </a>
                        @endif
                    </div>
                </section>
            @endif

            @if ($similarJobs->count() > 0)
                <section class="rounded-lg border border-gray-200 bg-white p-5" aria-labelledby="lowongan-serupa">
                    <h2 id="lowongan-serupa" class="mt-0 mb-2 text-base font-semibold text-gray-900">Lowongan serupa</h2>
                    <ul class="m-0 list-none divide-y divide-gray-200 p-0">
                        @foreach ($similarJobs as $similarJob)
                            <li class="py-3 last:pb-0">
                                <a href="{{ route('student.jobs.show', $similarJob->id) }}" class="text-sm font-medium text-gray-900 underline-offset-2 wrap-break-word hover:text-brand-700 hover:underline">{{ $similarJob->title }}</a>
                                <p class="m-0 text-xs text-gray-500 wrap-break-word">{{ collect([$similarJob->company->name ?? null, $similarJob->location])->filter()->implode(' · ') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
