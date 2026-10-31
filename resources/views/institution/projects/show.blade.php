@extends('layouts.app')

@section('title', $project->title)

@section('content')
@php
    // phase 19: detail proyek untuk pemantauan. tahap = linimasa milestone (urut 'order'); laporan = daftar bergaris pemisah.
    // angka dari data: milestone selesai/total, jumlah laporan. aksi kelola (milestone, status, laporan) tetap di /manage.
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $reports = $project->reports->sortByDesc(fn ($r) => ($r->period_end ?? $r->created_at)?->timestamp)->values();
    $pendingReports = $reports->where('status', 'pending')->count();
    $student = $project->student;
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header :title="$project->title" :description="collect([$student?->display_name, $student?->university?->name])->filter()->implode(' · ')"
                      :back="route('institution.projects.index')" back-label="Kembali ke daftar proyek">
        <x-slot:actions>
            <x-ui.button :href="route('institution.projects.manage', $project->id)" icon="settings">Kelola proyek</x-ui.button>
            @if ($project->status === 'completed')
                @if ($institutionReview)
                    <x-ui.button variant="secondary" :href="route('institution.reviews.show', $institutionReview->id)">Lihat ulasan</x-ui.button>
                @else
                    <x-ui.button variant="secondary" :href="route('institution.reviews.create', $project->id)" icon="pencil">Tulis ulasan</x-ui.button>
                @endif
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="sg-inst-detail">
        <div class="sg-inst-detail__main">
            <section class="sg-inst-detail__block" aria-labelledby="judul-tahap">
                <h2 id="judul-tahap">Tahap proyek</h2>
                @if ($project->milestones->count() > 0)
                    <ol class="sg-inst-timeline">
                        @foreach ($project->milestones as $milestone)
                            @php $done = $milestone->status === 'completed'; @endphp
                            <li>
                                <span class="sg-inst-timeline__dot" @if ($done) data-done @endif aria-hidden="true">@if ($done)<x-icon name="check" size="sm" />@endif</span>
                                <div class="sg-inst-timeline__body">
                                    <p class="sg-inst-timeline__title">
                                        {{ $milestone->title }}
                                        <x-ui.status domain="milestone" :value="$milestone->status" />
                                        @if ($milestone->requires_report)<span class="sg-inst-flag">Wajib laporan</span>@endif
                                    </p>
                                    @if (filled($milestone->description))
                                        <p class="sg-inst-detail__note">{{ $milestone->description }}</p>
                                    @endif
                                    <p class="sg-inst-timeline__meta">
                                        @if ($milestone->target_date)Target {{ $tanggal($milestone->target_date) }}@endif
                                        @if ($milestone->completed_at) · Selesai {{ $tanggal($milestone->completed_at) }}@endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <p class="sg-inst-detail__note">Belum ada tahap. Tambahkan milestone di halaman Kelola proyek.</p>
                @endif
            </section>

            <section class="sg-inst-detail__block" aria-labelledby="judul-laporan">
                <h2 id="judul-laporan">Laporan kemajuan</h2>
                @if ($reports->count() > 0)
                    <x-ui.list label="Laporan kemajuan proyek">
                        @foreach ($reports as $report)
                            <x-ui.list-item :title="$report->title" :meta="collect([$report->type_label, $report->period_start || $report->period_end ? trim(($tanggal($report->period_start) ?? '').' – '.($tanggal($report->period_end) ?? '')) : null])->filter()->implode(' · ')">
                                @if (filled($report->summary))
                                    <p class="sg-inst-review-item__text">{{ $report->summary }}</p>
                                @endif
                                @if (filled($report->institution_feedback))
                                    <p class="sg-inst-review-item__foot">Masukan instansi: {{ $report->institution_feedback }}</p>
                                @endif
                                @if ($report->document_path)
                                    <p class="sg-inst-review-item__foot"><a href="{{ document_url($report->document_path) }}" class="sg-inst-link" target="_blank" rel="noopener">Buka dokumen<span class="sr-only"> {{ $report->title }}</span></a></p>
                                @endif
                                <x-slot:end><x-ui.status domain="report" :value="$report->status" /></x-slot:end>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                @else
                    <p class="sg-inst-detail__note">Mahasiswa belum mengirim laporan.</p>
                @endif
            </section>

            @if (filled($project->description))
                <section class="sg-inst-detail__block">
                    <h2>Deskripsi</h2>
                    <div class="sg-inst-detail__text">{!! nl2br(e($project->description)) !!}</div>
                </section>
            @endif
        </div>

        <aside class="sg-inst-detail__rail" aria-label="Informasi proyek">
            <x-ui.dl>
                <div>
                    <dt>Status</dt>
                    <dd><x-ui.status domain="project" :value="$project->status" /></dd>
                </div>
                <div>
                    <dt>Tahap</dt>
                    <dd>@include('institution.projects.components.stage-meter', ['milestones' => $project->milestones])</dd>
                </div>
                <div>
                    <dt>Laporan</dt>
                    <dd>{{ $reports->count() }} laporan @if ($pendingReports) · {{ $pendingReports }} menunggu tinjauan @endif</dd>
                </div>
                @if ($project->problem)
                    <div>
                        <dt>Masalah</dt>
                        <dd><a href="{{ route('institution.problems.show', $project->problem_id) }}" class="sg-inst-link">{{ $project->problem->title }}</a></dd>
                    </div>
                @endif
            </x-ui.dl>

            <h2 class="sg-inst-detail__rail-h">Jadwal</h2>
            <x-ui.dl :items="[
                'Mulai' => $tanggal($project->start_date),
                'Rencana selesai' => $tanggal($project->end_date),
                'Selesai sebenarnya' => $tanggal($project->actual_end_date),
            ]" />

            <h2 class="sg-inst-detail__rail-h">Mahasiswa</h2>
            <x-ui.dl :items="[
                'Nama' => $student?->display_name,
                'Kampus' => $student?->university?->name,
                'Jurusan' => $student?->major,
            ]" />
            @if ($student?->user?->username)
                <a href="{{ route('profile.public', $student->user->username) }}" class="sg-inst-link">Lihat profil publik<span class="sr-only"> {{ $student->display_name }}</span></a>
            @endif
        </aside>
    </div>
</div>
@endsection
