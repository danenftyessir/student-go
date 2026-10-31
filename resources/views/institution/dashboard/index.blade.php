@extends('layouts.app')

@section('title', 'Dashboard Instansi')

@section('content')
@php
    // phase 17: dashboard sebagai antrean kerja (D-10). semua angka dari controller; nol tidak ditampilkan.
    $pendingApplications = (int) $urgentItems['pending_applications'];
    $activeCount = (int) $stats['projects']['active'];
    $reviewCount = (int) $urgentItems['pending_reviews'];
    $hasProblems = $stats['problems']['total'] > 0;
    $mainEmpty = $recentApplications->isEmpty() && $activeProjects->isEmpty();
    $asideEmpty = $pendingReviews->isEmpty() && $draftProblems->isEmpty() && $recentProblems->isEmpty();
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
@endphp

<div class="sg-container sg-inst-dash">
    <x-ui.page-header title="Dashboard" :description="$institution->name">
        <x-slot:actions>
            <x-ui.button :href="route('institution.problems.create')" icon="plus">Buat masalah baru</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- status verifikasi: blok pertama selama instansi belum terverifikasi (logika tampil tidak berubah) --}}
    @if(in_array($institution->verification_status, ['pending_verification', 'rejected', 'needs_review']))
        <div class="sg-inst-dash__verify">
            @if($institution->verification_status === 'pending_verification' || $institution->verification_status === null)
                <x-ui.alert title="Verifikasi instansi">
                    <x-ui.status domain="verification" :value="$institution->verification_status" />
                    <p>Instansi Anda belum terverifikasi. Unggah dokumen verifikasi agar statusnya dapat diperiksa.</p>
                    <div class="sg-inst-dash__actions">
                        <x-ui.button variant="secondary" :href="route('institution.verification.upload')" icon="upload">Unggah dokumen verifikasi</x-ui.button>
                    </div>
                </x-ui.alert>
            @elseif($institution->verification_status === 'rejected')
                <x-ui.alert tone="danger" title="Verifikasi instansi">
                    <x-ui.status domain="verification" :value="$institution->verification_status" />
                    <p>Dokumen verifikasi Anda belum lolos. Periksa hasil verifikasi, lalu unggah ulang dokumen yang sesuai.</p>
                    <div class="sg-inst-dash__actions">
                        <x-ui.button variant="secondary" :href="route('institution.verification.status')">Lihat hasil verifikasi</x-ui.button>
                        <x-ui.button variant="tertiary" :href="route('institution.verification.upload')" icon="upload">Unggah ulang dokumen</x-ui.button>
                    </div>
                </x-ui.alert>
            @elseif($institution->verification_status === 'needs_review')
                <x-ui.alert tone="warning" title="Verifikasi instansi">
                    <x-ui.status domain="verification" :value="$institution->verification_status" />
                    <p>Dokumen Anda perlu ditinjau secara manual. Status verifikasi akan diperbarui setelah peninjauan selesai.</p>
                    <div class="sg-inst-dash__actions">
                        <x-ui.button variant="secondary" :href="route('institution.verification.status')">Lihat detail verifikasi</x-ui.button>
                    </div>
                </x-ui.alert>
            @endif
        </div>
    @endif

    {{-- ringkasan antrean (pengganti kartu statistik dan chip tren) + dua pintasan --}}
    <div class="sg-inst-dash__bar">
        <x-ui.summary label="Antrean kerja" :items="[
            ['value' => $pendingApplications ?: null, 'label' => 'lamaran menunggu keputusan'],
            ['value' => $activeCount ?: null, 'label' => 'proyek berjalan'],
            ['value' => $reviewCount ?: null, 'label' => 'proyek selesai menunggu ulasan'],
            ['value' => $draftProblemsCount ?: null, 'label' => 'masalah draf'],
        ]" />
        <nav class="sg-inst-dash__shortcuts" aria-label="Pintasan">
            <x-ui.button variant="tertiary" :href="route('institution.applications.index', ['status' => 'pending'])" icon="clipboard-list">Tinjau lamaran</x-ui.button>
            <x-ui.button variant="tertiary" :href="route('institution.projects.index')" icon="folder-open">Pantau proyek</x-ui.button>
        </nav>
    </div>

    <div @class(['sg-inst-dash__grid', 'sg-inst-dash__grid--single' => $asideEmpty])>
        <div class="sg-inst-dash__col">
            @if($mainEmpty)
                @if($hasProblems)
                    <x-ui.empty-state illustration="no-applications"
                        title="Tidak ada lamaran yang menunggu atau proyek berjalan"
                        description="Lamaran baru muncul di sini setelah mahasiswa melamar masalah Anda yang dibuka." />
                @else
                    <x-ui.empty-state illustration="no-applications"
                        title="Belum ada masalah yang diterbitkan"
                        description="Mulai dengan tombol Buat masalah baru di atas. Lamaran mahasiswa yang menunggu keputusan akan muncul di halaman ini." />
                @endif
            @endif

            {{-- 1. lamaran yang menunggu keputusan --}}
            @if($recentApplications->isNotEmpty())
                <section aria-labelledby="antrean-lamaran">
                    <div class="sg-inst-dash__head">
                        <h2 id="antrean-lamaran">Lamaran menunggu keputusan</h2>
                        <a href="{{ route('institution.applications.index') }}" class="sg-inst-dash__more">Lihat semua lamaran</a>
                    </div>
                    @if($pendingApplications > $recentApplications->count())
                        <p class="sg-inst-dash__note">{{ $recentApplications->count() }} terbaru dari {{ $pendingApplications }} lamaran yang menunggu.</p>
                    @endif
                    <x-ui.list label="Lamaran menunggu keputusan">
                        @foreach($recentApplications as $application)
                            <x-ui.list-item :title="$application->student->display_name"
                                :href="route('institution.applications.show', $application->id)"
                                :meta="$application->problem->title">
                                <x-slot:leading><x-ui.avatar :name="$application->student->display_name" size="sm" decorative /></x-slot:leading>
                                <p class="sg-inst-dash__detail">
                                    @if($application->student->university){{ $application->student->university->name }} · @endif
                                    Diajukan {{ $tanggal($application->created_at) }}
                                </p>
                                <x-slot:end><x-ui.status domain="application" :value="$application->status" /></x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                </section>
            @endif

            {{-- 2. proyek berjalan: milestone berikutnya dan laporan yang menunggu tinjauan --}}
            @if($activeProjects->isNotEmpty())
                <section aria-labelledby="antrean-proyek">
                    <div class="sg-inst-dash__head">
                        <h2 id="antrean-proyek">Proyek berjalan</h2>
                        <a href="{{ route('institution.projects.index', ['status' => 'active']) }}" class="sg-inst-dash__more">Lihat semua</a>
                    </div>
                    @if($activeCount > $activeProjects->count())
                        <p class="sg-inst-dash__note">{{ $activeProjects->count() }} dari {{ $activeCount }} proyek berjalan.</p>
                    @endif
                    <x-ui.list label="Proyek berjalan">
                        @foreach($activeProjects as $project)
                            @php($next = $project->milestones->first())
                            @php($reportDue = $project->milestones->firstWhere('requires_report', true))
                            <x-ui.list-item :title="$project->title"
                                :href="route('institution.projects.show', $project->id)"
                                :meta="$project->student->display_name">
                                @if($reportDue)
                                    <p @class(['sg-inst-dash__detail', 'sg-inst-dash__detail--warn' => $reportDue->is_overdue])>
                                        <x-icon name="file-text" size="sm" />
                                        Laporan jatuh tempo: {{ $reportDue->title }} · {{ $tanggal($reportDue->target_date) }}@if($reportDue->is_overdue) (lewat tenggat)@endif
                                    </p>
                                @endif
                                @if($next && (! $reportDue || $next->isNot($reportDue)))
                                    <p @class(['sg-inst-dash__detail', 'sg-inst-dash__detail--warn' => $next->is_overdue])>
                                        <x-icon name="calendar" size="sm" />
                                        Milestone berikutnya: {{ $next->title }} · target {{ $tanggal($next->target_date) }}@if($next->is_overdue) (lewat target)@endif
                                    </p>
                                @endif
                                @if($project->pending_reports_count > 0)
                                    <p class="sg-inst-dash__detail">
                                        <x-icon name="file-text" size="sm" />
                                        {{ $project->pending_reports_count }} laporan menunggu tinjauan
                                    </p>
                                @endif
                                <x-slot:end>
                                    <span class="sg-inst-dash__progress">
                                        <progress value="{{ (int) round($project->progress_percentage ?? 0) }}" max="100" aria-label="Progres {{ $project->title }}"></progress>
                                        {{ (int) round($project->progress_percentage ?? 0) }}%
                                    </span>
                                </x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                </section>
            @endif
        </div>

        @unless($asideEmpty)
            <div class="sg-inst-dash__col">
                {{-- 3. proyek selesai yang menunggu ulasan --}}
                @if($pendingReviews->isNotEmpty())
                    <section aria-labelledby="antrean-ulasan">
                        <div class="sg-inst-dash__head">
                            <h2 id="antrean-ulasan">Menunggu ulasan</h2>
                            <a href="{{ route('institution.reviews.index') }}" class="sg-inst-dash__more">Lihat semua</a>
                        </div>
                        @if($reviewCount > $pendingReviews->count())
                            <p class="sg-inst-dash__note">{{ $pendingReviews->count() }} dari {{ $reviewCount }} proyek selesai.</p>
                        @endif
                        <x-ui.list label="Proyek selesai yang menunggu ulasan">
                            @foreach($pendingReviews as $project)
                                <x-ui.list-item :title="$project->title" :meta="$project->student->display_name">
                                    <p class="sg-inst-dash__detail">Selesai {{ $tanggal($project->actual_end_date) }}</p>
                                    <x-slot:end>
                                        <a href="{{ route('institution.reviews.create', $project->id) }}" class="sg-inst-dash__more">Tulis ulasan<span class="sr-only"> untuk {{ $project->title }}, {{ $project->student->display_name }}</span></a>
                                    </x-slot:end>
                                </x-ui.list-item>
                            @endforeach
                        </x-ui.list>
                    </section>
                @endif

                {{-- 4. masalah draf --}}
                @if($draftProblems->isNotEmpty())
                    <section aria-labelledby="antrean-draf">
                        <div class="sg-inst-dash__head">
                            <h2 id="antrean-draf">Masalah draf</h2>
                            <a href="{{ route('institution.problems.index', ['status' => 'draft']) }}" class="sg-inst-dash__more">Lihat semua</a>
                        </div>
                        <x-ui.list label="Masalah draf">
                            @foreach($draftProblems as $problem)
                                <x-ui.list-item :title="$problem->title"
                                    :href="route('institution.problems.edit', $problem->id)"
                                    :meta="'Terakhir diubah ' . $tanggal($problem->updated_at)">
                                    <x-slot:end><x-ui.status domain="problem" :value="$problem->status" /></x-slot:end>
                                </x-ui.list-item>
                            @endforeach
                        </x-ui.list>
                    </section>
                @endif

                {{-- masalah terbaru (rujukan, bukan antrean) --}}
                @if($recentProblems->isNotEmpty())
                    <section aria-labelledby="masalah-terbaru">
                        <div class="sg-inst-dash__head">
                            <h2 id="masalah-terbaru">Masalah terbaru</h2>
                            <a href="{{ route('institution.problems.index') }}" class="sg-inst-dash__more">Lihat semua</a>
                        </div>
                        <x-ui.list label="Masalah terbaru">
                            @foreach($recentProblems as $problem)
                                <x-ui.list-item :title="$problem->title"
                                    :href="route('institution.problems.show', $problem->id)"
                                    :meta="$problem->applications_count . ' lamaran'">
                                    <x-slot:end><x-ui.status domain="problem" :value="$problem->status" /></x-slot:end>
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
