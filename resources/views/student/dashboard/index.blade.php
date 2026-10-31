@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

{{-- phase 7: dashboard sebagai antrean kerja (ruang kerja). tanpa foto, tanpa tile, setiap angka sekali dan dari query controller (D-10).
     urutan dom: header, langkah berikutnya, rekomendasi, jaringan, proyek berjalan, lamaran terbaru. --}}

@php
    $user = Auth::user();
    $university = optional($student->university)->name;

    // label kolom kelengkapan profil (DashboardController::calculateProfileCompletion)
    $missingLabels = [
        'profile_photo' => 'foto profil',
        'bio' => 'bio',
        'skills' => 'keahlian',
        'whatsapp' => 'nomor WhatsApp',
        'semester' => 'semester',
    ];
    $missing = collect($profileCompletion['missing_fields'])->map(fn ($field) => $missingLabels[$field] ?? $field);

    // tanggal dan sisa hari dalam bahasa indonesia
    $formatDate = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y') : null;
    $daysLeft = function ($date) {
        $days = (int) today()->diffInDays($date, false);
        return match (true) {
            $days <= 0 => 'hari ini',
            $days === 1 => 'besok',
            default => $days . ' hari lagi',
        };
    };

    $hasQueue = $upcomingMilestones->isNotEmpty()
        || $friendRequests->isNotEmpty()
        || $stats['pending_applications'] > 0
        || ! $profileCompletion['is_complete'];

    $suggestions = $network['suggestions'] >= 10 ? '10+' : $network['suggestions'];
@endphp

@section('content')
<div class="sg-container sg-dash">

    <x-ui.page-header
        :title="'Selamat datang, ' . $user->display_name"
        :description="collect([$student->major, $university])->filter()->implode(' · ')">
        <x-slot:actions>
            <x-ui.button :href="route('student.browse-problems.index')" icon="search">Telusuri Masalah</x-ui.button>
            <x-ui.button variant="secondary" :href="route('student.applications.index')">Aplikasi Saya</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="sg-dash__grid">

        {{-- langkah berikutnya: tenggat milestone, permintaan koneksi, lamaran menunggu, profil --}}
        <section class="sg-dash__queue" aria-labelledby="dash-queue-title">
            <h2 id="dash-queue-title" class="sg-dash__h2">Langkah berikutnya</h2>

            @if ($hasQueue)
                <ul class="sg-list sg-dash-queue">
                    @foreach ($upcomingMilestones as $milestone)
                        @php $project = $activeProjects->firstWhere('id', $milestone->project_id); @endphp
                        <li class="sg-dash-queue__item">
                            <span class="sg-dash-queue__icon"><x-icon name="calendar" /></span>
                            <div class="sg-dash-queue__main">
                                @if ($loop->first)<p class="sg-dash-queue__kicker">Tenggat terdekat</p>@endif
                                <p class="sg-dash-queue__title">{{ $milestone->title }}</p>
                                <p class="sg-dash-queue__meta">
                                    @if ($project && $project->problem){{ $project->problem->title }} · @endif
                                    <time datetime="{{ $milestone->target_date->toDateString() }}">{{ $formatDate($milestone->target_date) }}</time>, <span class="whitespace-nowrap">{{ $daysLeft($milestone->target_date) }}</span>
                                </p>
                            </div>
                            <div class="sg-dash-queue__end">
                                <x-ui.status domain="milestone" :value="$milestone->status" />
                                <x-ui.button variant="tertiary" :href="route('student.projects.show', $milestone->project_id)">
                                    Buka proyek<span class="sr-only">: {{ $milestone->title }}</span>
                                </x-ui.button>
                            </div>
                        </li>
                    @endforeach

                    @foreach ($friendRequests as $friendRequest)
                        <li class="sg-dash-queue__item">
                            <x-ui.avatar :src="$friendRequest->requester->profile_photo_url" :name="$friendRequest->requester->display_name" size="sm" decorative class="sg-dash-queue__avatar" />
                            <div class="sg-dash-queue__main">
                                <p class="sg-dash-queue__title">{{ $friendRequest->requester->display_name }} ingin terhubung</p>
                                <p class="sg-dash-queue__meta">{{ collect([$friendRequest->requester->major, optional($friendRequest->requester->university)->name])->filter()->implode(' · ') }}</p>
                            </div>
                            <div class="sg-dash-queue__end">
                                <form method="POST" action="{{ route('student.friends.accept', $friendRequest->id) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" icon="user-check">
                                        Terima<span class="sr-only"> {{ $friendRequest->requester->display_name }}</span>
                                    </x-ui.button>
                                </form>
                                <form method="POST" action="{{ route('student.friends.reject', $friendRequest->id) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="tertiary">
                                        Tolak<span class="sr-only"> {{ $friendRequest->requester->display_name }}</span>
                                    </x-ui.button>
                                </form>
                            </div>
                        </li>
                    @endforeach

                    @if ($stats['pending_applications'] > 0)
                        <li class="sg-dash-queue__item">
                            <span class="sg-dash-queue__icon"><x-icon name="hourglass" /></span>
                            <div class="sg-dash-queue__main">
                                <p class="sg-dash-queue__title">{{ $stats['pending_applications'] }} lamaran menunggu keputusan instansi</p>
                                <p class="sg-dash-queue__meta">Instansi akan meninjau lamaran Anda. Statusnya berubah di Aplikasi Saya.</p>
                            </div>
                            <div class="sg-dash-queue__end">
                                <x-ui.button variant="tertiary" :href="route('student.applications.index', ['status' => 'pending'])">Lihat lamaran</x-ui.button>
                            </div>
                        </li>
                    @endif

                    @unless ($profileCompletion['is_complete'])
                        <li class="sg-dash-queue__item">
                            <span class="sg-dash-queue__icon"><x-icon name="user" /></span>
                            <div class="sg-dash-queue__main">
                                <p class="sg-dash-queue__title">Lengkapi profil Anda</p>
                                <p class="sg-dash-queue__meta">Profil {{ $profileCompletion['percentage'] }}% lengkap. Belum diisi: {{ $missing->implode(', ') }}.</p>
                            </div>
                            <div class="sg-dash-queue__end">
                                <x-ui.button variant="tertiary" :href="route('student.profile.edit')">Lengkapi profil</x-ui.button>
                            </div>
                        </li>
                    @endunless
                </ul>
            @else
                <p class="sg-dash__none">Tidak ada yang perlu Anda tindak lanjuti saat ini.</p>
            @endif
        </section>

        <div class="sg-dash__rail">
            {{-- rekomendasi: logika controller tidak berubah (dibuka, tenggat belum lewat, kuota belum penuh, jurusan) --}}
            <section aria-labelledby="dash-reco-title">
                <div class="sg-dash__head">
                    <h2 id="dash-reco-title" class="sg-dash__h2">Rekomendasi masalah</h2>
                    <a href="{{ route('student.browse-problems.index') }}" class="sg-dash__more">Lihat semua</a>
                </div>
                @if ($recommendedProblems->isNotEmpty())
                    <ul class="sg-list sg-dash-reco">
                        @foreach ($recommendedProblems->take(3) as $problem)
                            <li class="sg-dash-reco__item">
                                <p class="sg-dash-reco__title">{{ $problem->title }}</p>
                                <p class="sg-dash-reco__meta">{{ optional($problem->institution)->name }}</p>
                                <p class="sg-dash-reco__meta">
                                    <x-icon name="map-pin" size="sm" />{{ collect([optional($problem->regency)->name, optional($problem->province)->name])->filter()->implode(', ') }}
                                </p>
                                <div class="sg-dash-reco__foot">
                                    <span class="sg-dash-reco__deadline">Ditutup <time datetime="{{ $problem->application_deadline->toDateString() }}">{{ $formatDate($problem->application_deadline) }}</time></span>
                                    <x-ui.button variant="tertiary" :href="route('student.browse-problems.show', $problem->id)" icon-end="arrow-right">
                                        Lihat<span class="sr-only"> {{ $problem->title }}</span>
                                    </x-ui.button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="sg-dash__none">Belum ada rekomendasi untuk Anda saat ini. Telusuri semua masalah yang sedang dibuka.</p>
                @endif
            </section>

            {{-- jaringan: satu baris ringkasan; aksi hubungkan ada di halaman jaringan --}}
            <section class="sg-dash__network" aria-labelledby="dash-network-title">
                <h2 id="dash-network-title" class="sg-dash__h2">Jaringan</h2>
                <x-ui.summary label="Ringkasan jaringan" :items="[
                    ['value' => $network['connections'], 'label' => 'koneksi'],
                    ['value' => $network['requests'] > 0 ? $network['requests'] : null, 'label' => 'permintaan'],
                    ['value' => $network['suggestions'] > 0 ? $suggestions : null, 'label' => 'saran'],
                ]" />
                <a href="{{ route('student.friends.index') }}" class="sg-dash__more">Buka Jaringan</a>
            </section>
        </div>

        {{-- proyek berjalan (status 'active'); progres dari milestone yang selesai --}}
        @if ($activeProjects->isNotEmpty())
            <section class="sg-dash__projects" aria-labelledby="dash-projects-title">
                <div class="sg-dash__head">
                    <h2 id="dash-projects-title" class="sg-dash__h2">Proyek berjalan</h2>
                    <a href="{{ route('student.projects.index') }}" class="sg-dash__more">Lihat semua</a>
                </div>
                <ul class="sg-list">
                    @foreach ($activeProjects as $project)
                        @php $progress = (int) ($project->progress_percentage ?? 0); @endphp
                        <li class="sg-dash-project">
                            <div class="sg-dash-project__main">
                                <p class="sg-dash-project__title">
                                    <a href="{{ route('student.projects.show', $project->id) }}">{{ optional($project->problem)->title ?? $project->title }}</a>
                                </p>
                                <p class="sg-dash-queue__meta">{{ optional($project->institution)->name }}</p>
                            </div>
                            <div class="sg-dash-project__progress">
                                <progress max="100" value="{{ $progress }}" aria-label="Progres {{ optional($project->problem)->title ?? $project->title }}">{{ $progress }}%</progress>
                                <span>{{ $progress }}%</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- lamaran terbaru: 5 terakhir, status dari kosakata bersama --}}
        <section class="sg-dash__apps" aria-labelledby="dash-apps-title">
            <div class="sg-dash__head">
                <h2 id="dash-apps-title" class="sg-dash__h2">Lamaran terbaru</h2>
                @if ($recentApplications->isNotEmpty())
                    <a href="{{ route('student.applications.index') }}" class="sg-dash__more">Lihat semua</a>
                @endif
            </div>

            @if ($recentApplications->isNotEmpty())
                <x-ui.table caption="Lima lamaran KKN terbaru">
                    <x-slot:head>
                        <th scope="col">Masalah</th>
                        <th scope="col">Lokasi</th>
                        <th scope="col">Diajukan</th>
                        <th scope="col">Status</th>
                    </x-slot:head>
                    @foreach ($recentApplications as $application)
                        @php $applied = $application->applied_at ?? $application->created_at; @endphp
                        <tr>
                            <td data-label="Masalah">
                                <div>
                                    <a href="{{ route('student.applications.show', $application->id) }}" class="sg-table__primary">{{ optional($application->problem)->title ?? 'Masalah tidak tersedia' }}</a>
                                    <span class="sg-table__meta">{{ optional(optional($application->problem)->institution)->name }}</span>
                                </div>
                            </td>
                            <td data-label="Lokasi"><span>{{ optional(optional($application->problem)->regency)->name ?? '—' }}</span></td>
                            <td data-label="Diajukan"><time datetime="{{ $applied->toDateString() }}">{{ $formatDate($applied) }}</time></td>
                            <td data-label="Status"><span><x-ui.status domain="application" :value="$application->status" /></span></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @else
                <x-ui.empty-state
                    title="Belum ada lamaran KKN"
                    description="Pilih masalah dari instansi yang sedang membuka pendaftaran, lalu ajukan lamaran pertama Anda."
                    illustration="no-applications"
                    :heading-level="3"
                    class="sg-dash__empty">
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="route('student.browse-problems.index')">Telusuri masalah KKN</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @endif
        </section>
    </div>
</div>
@endsection
