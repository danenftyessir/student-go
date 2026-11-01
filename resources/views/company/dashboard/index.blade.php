@extends('layouts.app')

@section('title', 'Dashboard Perusahaan')

@section('content')
@php
    // phase 21: dashboard sebagai antrean kerja (D-10). semua angka dari controller; nol tidak ditampilkan.
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $asideEmpty = $savedTalents->isEmpty() && $talentRecommendations->isEmpty();
@endphp

<div class="sg-container sg-co-dash">
    <x-ui.page-header title="Dashboard" :description="$company->name">
        <x-slot:actions>
            <x-ui.button :href="route('company.jobs.create')" icon="plus">Pasang lowongan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- ringkasan lowongan (pengganti kartu statistik dan chip tren) + dua pintasan --}}
    <div class="sg-co-dash__bar">
        <x-ui.summary label="Ringkasan lowongan" :items="[
            ['value' => $activeJobsCount ?: null, 'label' => 'lowongan aktif'],
            ['value' => $draftJobsCount ?: null, 'label' => 'lowongan draf'],
        ]" />
        <nav class="sg-co-dash__shortcuts" aria-label="Pintasan">
            <x-ui.button variant="tertiary" :href="route('company.applications.index')" icon="clipboard-list">Tinjau lamaran</x-ui.button>
            <x-ui.button variant="tertiary" :href="route('company.talents.index')" icon="search">Cari talenta</x-ui.button>
        </nav>
    </div>

    <div @class(['sg-co-dash__grid', 'sg-co-dash__grid--single' => $asideEmpty])>
        <div class="sg-co-dash__col">
            @if($totalJobs === 0)
                {{-- akun baru: alur perusahaan (diadaptasi dari workflows/company-journey.svg) dengan tautan ke halaman nyata --}}
                <section aria-labelledby="mulai">
                    <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran"
                        description="Lamaran mahasiswa muncul di sini setelah Anda memasang lowongan. Mulai dengan tombol Pasang lowongan di atas." />
                    <h2 id="mulai" class="sg-co-dash__steps-title">Cara kerja di Student Go</h2>
                    <ol class="sg-co-dash__steps">
                        <li>
                            <a href="{{ route('company.talents.index') }}">Cari talenta</a>
                            <p>Telusuri mahasiswa dengan pencarian dan filter.</p>
                        </li>
                        <li>
                            <a href="{{ route('company.talents.saved') }}">Simpan dan bandingkan</a>
                            <p>Kumpulkan kandidat ke Talenta Tersimpan, lalu bandingkan.</p>
                        </li>
                        <li>
                            <a href="{{ route('company.jobs.create') }}">Terbitkan lowongan</a>
                            <p>Susun lowongan lewat formulir lima langkah.</p>
                        </li>
                        <li>
                            <a href="{{ route('company.applications.index') }}">Kelola pelamar</a>
                            <p>Pantau pelamar per tahap di halaman Lamaran.</p>
                        </li>
                    </ol>
                </section>
            @elseif($totalApplications === 0)
                @if($activeJobsCount > 0)
                    <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran"
                        description="Lamaran muncul di sini setelah mahasiswa melamar lowongan aktif Anda. Sementara itu, Anda dapat mencari talenta dan menyimpannya." />
                @else
                    <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran"
                        description="Belum ada lowongan yang dibuka. Terbitkan lowongan draf Anda atau pasang lowongan baru agar mahasiswa dapat melamar.">
                        <x-slot:action>
                            <x-ui.button variant="secondary" :href="route('company.jobs.index')" icon="briefcase">Lihat lowongan</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @endif
            @endif

            {{-- 1. pelamar per tahap (hanya tahap yang masih berjalan dan tidak nol) --}}
            @if($stages->isNotEmpty())
                <section aria-labelledby="pelamar-tahap">
                    <div class="sg-co-dash__head">
                        <h2 id="pelamar-tahap">Pelamar per tahap</h2>
                        <a href="{{ route('company.applications.index') }}" class="sg-co-dash__more">Buka papan lamaran</a>
                    </div>
                    <ol class="sg-co-dash__stages" aria-label="Jumlah pelamar per tahap">
                        @foreach($stages as $stage)
                            <li>
                                <span class="sg-co-dash__stage-count">{{ number_format($stage['total'], 0, ',', '.') }}</span>
                                <span class="sg-co-dash__stage-label">{{ \App\Support\StatusVocabulary::label('job_application', $stage['status']) }}</span>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- 2. lamaran terbaru --}}
            @if($recentApplications->isNotEmpty())
                <section aria-labelledby="lamaran-terbaru">
                    <div class="sg-co-dash__head">
                        <h2 id="lamaran-terbaru">Lamaran terbaru</h2>
                        <a href="{{ route('company.applications.index') }}" class="sg-co-dash__more">Lihat semua lamaran</a>
                    </div>
                    @if($totalApplications > $recentApplications->count())
                        <p class="sg-co-dash__note">{{ $recentApplications->count() }} terbaru dari {{ number_format($totalApplications, 0, ',', '.') }} lamaran.</p>
                    @endif
                    <x-ui.list label="Lamaran terbaru">
                        @foreach($recentApplications as $application)
                            <x-ui.list-item :title="$application['name']"
                                :href="route('company.applications.show', $application['id'])"
                                :meta="$application['position']">
                                <x-slot:leading><x-ui.avatar :name="$application['name']" size="sm" decorative /></x-slot:leading>
                                <p class="sg-co-dash__detail">Melamar {{ $tanggal($application['created_at']) }}</p>
                                <x-slot:end><x-ui.status domain="job_application" :value="$application['status']" /></x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                </section>
            @endif

            {{-- 3. lowongan aktif (tabel) --}}
            @if($activeJobs->isNotEmpty())
                <section aria-labelledby="lowongan-aktif">
                    <div class="sg-co-dash__head">
                        <h2 id="lowongan-aktif">Lowongan aktif</h2>
                        <a href="{{ route('company.jobs.index') }}" class="sg-co-dash__more">Kelola lowongan</a>
                    </div>
                    @if($activeJobsCount > $activeJobs->count())
                        <p class="sg-co-dash__note">{{ $activeJobs->count() }} terbaru dari {{ $activeJobsCount }} lowongan aktif.</p>
                    @endif
                    <x-ui.table caption="Lowongan aktif">
                        <x-slot:head>
                            <th scope="col">Lowongan</th>
                            <th scope="col" class="sg-num">Pelamar</th>
                            <th scope="col">Ditayangkan</th>
                            <th scope="col">Berakhir</th>
                        </x-slot:head>
                        @foreach($activeJobs as $job)
                            <tr>
                                <td data-label="Lowongan">
                                    <a href="{{ route('company.jobs.show', $job->id) }}" class="sg-table__primary">{{ $job->title }}</a>
                                    @if($job->jobCategory || $job->location)
                                        <span class="sg-table__meta">{{ collect([$job->jobCategory?->name, $job->location])->filter()->implode(' · ') }}</span>
                                    @endif
                                </td>
                                <td data-label="Pelamar" class="sg-num">
                                    @if($job->job_applications_count > 0)
                                        <a href="{{ route('company.applications.index', ['job_posting_id' => $job->id]) }}">{{ $job->job_applications_count }}<span class="sr-only"> pelamar untuk {{ $job->title }}</span></a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td data-label="Ditayangkan">{{ $tanggal($job->published_at) }}</td>
                                <td data-label="Berakhir">{{ $tanggal($job->expires_at) ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </section>
            @endif

            {{-- 4. grafik lamaran per bulan: hanya bila >= 5 bulan berisi data (D-21) --}}
            @if($applicationsChart)
                <section aria-labelledby="lamaran-per-bulan">
                    <div class="sg-co-dash__head">
                        <h2 id="lamaran-per-bulan">Lamaran masuk per bulan</h2>
                    </div>
                    <figure class="sg-co-dash__chart">
                        <div class="sg-co-dash__canvas">
                            {{-- digambar oleh resources/js/student-go/charts.js (chart.js lokal, dimuat hanya bila ada grafik) --}}
                            <canvas id="grafik-lamaran" aria-hidden="true" data-sg-chart="{{ json_encode($applicationsChart) }}" data-sg-chart-label="Lamaran masuk"></canvas>
                        </div>
                        {{-- sr-only pada pembungkus: tabel mengabaikan lebar 1px dan akan melebarkan halaman --}}
                        <div class="sr-only">
                            {{-- pembungkus overflow-x: tabel menggulir di dalam wadahnya sendiri (phase 29 F, C3) --}}
                            <div class="sg-co-dash__table-scroll">
                            <table>
                                <caption>Jumlah lamaran masuk per bulan, enam bulan terakhir</caption>
                                <thead><tr><th scope="col">Bulan</th><th scope="col">Lamaran</th></tr></thead>
                                <tbody>
                                    @foreach($applicationsChart as $month)
                                        <tr><th scope="row">{{ $month['label'] }}</th><td>{{ $month['total'] }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <figcaption class="sg-co-dash__note">Semua lamaran ke lowongan Anda, menurut tanggal masuk.</figcaption>
                    </figure>
                </section>
            @endif
        </div>

        @unless($asideEmpty)
            <div class="sg-co-dash__col">
                {{-- 5. talenta tersimpan --}}
                @if($savedTalents->isNotEmpty())
                    <section aria-labelledby="talenta-tersimpan">
                        <div class="sg-co-dash__head">
                            <h2 id="talenta-tersimpan">Talenta tersimpan</h2>
                            <a href="{{ route('company.talents.saved') }}" class="sg-co-dash__more">Lihat semua</a>
                        </div>
                        @if($savedTalentsCount > $savedTalents->count())
                            <p class="sg-co-dash__note">{{ $savedTalents->count() }} terakhir dari {{ $savedTalentsCount }} talenta tersimpan.</p>
                        @endif
                        <x-ui.list label="Talenta tersimpan">
                            @foreach($savedTalents as $talent)
                                <x-ui.list-item :title="$talent->display_name"
                                    :href="$talent->username ? route('profile.public', $talent->username) : null"
                                    :meta="$talent->student?->major">
                                    <x-slot:leading><x-ui.avatar :name="$talent->display_name" size="sm" decorative /></x-slot:leading>
                                </x-ui.list-item>
                            @endforeach
                        </x-ui.list>
                    </section>
                @endif

                {{-- 6. talenta terbaru (logika saran tidak berubah: mahasiswa terbaru yang belum melamar lowongan Anda) --}}
                @if($talentRecommendations->isNotEmpty())
                    <section aria-labelledby="talenta-terbaru">
                        <div class="sg-co-dash__head">
                            <h2 id="talenta-terbaru">Talenta terbaru</h2>
                            <a href="{{ route('company.talents.index') }}" class="sg-co-dash__more">Lihat semua</a>
                        </div>
                        <p class="sg-co-dash__note">Mahasiswa yang baru bergabung dan belum melamar lowongan Anda.</p>
                        <x-ui.list label="Talenta terbaru">
                            @foreach($talentRecommendations as $talent)
                                <x-ui.list-item :title="$talent['name']"
                                    :href="$talent['username'] ? route('profile.public', $talent['username']) : null"
                                    :meta="$talent['expertise']">
                                    <x-slot:leading><x-ui.avatar :name="$talent['name']" size="sm" decorative /></x-slot:leading>
                                </x-ui.list-item>
                            @endforeach
                        </x-ui.list>
                    </section>
                @endif
            </div>
        @endunless
    </div>
</div>
@endsection
