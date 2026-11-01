@extends('layouts.app')

@section('title', 'Kelola Proyek')

@section('content')
@php
    // phase 19: tabel pemantauan proyek (Proyek · Mahasiswa · Status · Tahap · Laporan terakhir · Jadwal · Aksi).
    // filter lama tetap: search, status, problem_id. tahap dari milestone; laporan terakhir = periode paling akhir.
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : '—';
    $statusOptions = collect(\App\Support\StatusVocabulary::values('project'))->map(fn ($entry) => $entry[0])->all();
    $problemOptions = $problems->pluck('title', 'id')->all();
    $filtered = request()->filled('search') || request()->filled('status') || request()->filled('problem_id');
    $activeFilters = (int) request()->filled('status') + (int) request()->filled('problem_id');
    // controller mem-paginate tanpa withQueryString; parameter filter dibawa di tautan halaman
    $projects->appends(request()->only(['search', 'status', 'problem_id', 'sort']));
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Proyek" description="Pantau tahap dan laporan proyek mahasiswa yang sedang dan sudah berjalan." />

    @if ($stats['total'] > 0)
        <x-ui.summary label="Ringkasan proyek" class="sg-inst-page__summary" :items="[
            ['value' => $stats['total'], 'label' => 'proyek'],
            ['value' => $stats['active'] ?: null, 'label' => 'berjalan'],
            ['value' => $stats['on_hold'] ?: null, 'label' => 'ditunda'],
            ['value' => $stats['completed'] ?: null, 'label' => 'selesai'],
            ['value' => $stats['cancelled'] ?: null, 'label' => 'dibatalkan'],
        ]" />

        <x-ui.filter-bar :action="route('institution.projects.index')" search-label="Cari proyek" search-placeholder="Cari judul proyek atau nama mahasiswa"
                         :reset-href="route('institution.projects.index')" :active="$activeFilters" id="filter-proyek" class="sg-inst-page__filters">
            <x-ui.field label="Status" for="status">
                <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" />
            </x-ui.field>
            <x-ui.field label="Masalah" for="problem_id">
                <x-ui.select name="problem_id" :options="$problemOptions" :selected="request('problem_id')" placeholder="Semua masalah" />
            </x-ui.field>
        </x-ui.filter-bar>
    @endif

    @if ($projects->count() > 0)
        <x-ui.table caption="Daftar proyek instansi">
            <x-slot:head>
                <th scope="col">Proyek</th>
                <th scope="col">Mahasiswa</th>
                <th scope="col">Status</th>
                <th scope="col">Tahap</th>
                <th scope="col">Laporan terakhir</th>
                <th scope="col">Jadwal</th>
                <th scope="col">Aksi</th>
            </x-slot:head>
            @foreach ($projects as $project)
                @php
                    $lastReport = $project->reports->sortByDesc(fn ($r) => ($r->period_end ?? $r->created_at)?->timestamp)->first();
                    $pendingReports = $project->reports->where('status', 'pending')->count();
                    $overdue = $project->status === 'active' && $project->end_date && $project->end_date->isPast();
                @endphp
                <tr>
                    <td data-label="Proyek">
                        <a href="{{ route('institution.projects.show', $project->id) }}" class="sg-table__primary">{{ $project->title }}</a>
                        @if ($project->problem && $project->problem->title !== $project->title)
                            <span class="sg-table__meta">{{ $project->problem->title }}</span>
                        @endif
                    </td>
                    <td data-label="Mahasiswa">
                        <span>{{ $project->student?->display_name }}</span>
                        <span class="sg-table__meta">{{ $project->student?->university?->name }}</span>
                    </td>
                    <td data-label="Status"><x-ui.status domain="project" :value="$project->status" /></td>
                    <td data-label="Tahap">@include('institution.projects.components.stage-meter', ['milestones' => $project->milestones])</td>
                    <td data-label="Laporan terakhir">
                        @if ($lastReport)
                            <span>{{ $lastReport->type_label }}</span>
                            <span class="sg-table__meta">s.d. {{ $tanggal($lastReport->period_end ?? $lastReport->created_at) }}</span>
                            <x-ui.status domain="report" :value="$lastReport->status" />
                            @if ($pendingReports > 0 && ! ($pendingReports === 1 && $lastReport->status === 'pending'))<span class="sg-table__meta">{{ $pendingReports }} laporan menunggu tinjauan</span>@endif
                        @else
                            <span class="sg-inst-detail__note">Belum ada laporan</span>
                        @endif
                    </td>
                    <td data-label="Jadwal">
                        <span>{{ $tanggal($project->start_date) }} – {{ $tanggal($project->end_date) }}</span>
                        @if ($overdue)<span class="sg-table__meta sg-inst-warn">Melewati tanggal selesai</span>@endif
                    </td>
                    <td data-label="Aksi">
                        <span class="sg-inst-actions">
                            <a href="{{ route('institution.projects.show', $project->id) }}">Detail<span class="sr-only"> {{ $project->title }}, {{ $project->student?->display_name }}</span></a>
                            <a href="{{ route('institution.projects.manage', $project->id) }}">Kelola<span class="sr-only"> {{ $project->title }}, {{ $project->student?->display_name }}</span></a>
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.pagination :paginator="$projects" label="Halaman daftar proyek" class="sg-inst-page__pagination" />
    @elseif ($filtered)
        <x-ui.empty-state illustration="no-search-results" title="Tidak ada proyek yang cocok"
                          description="Ubah kata kunci, status atau masalah, atau tampilkan semua proyek.">
            <x-slot:action>
                <x-ui.button variant="secondary" :href="route('institution.projects.index')">Tampilkan semua proyek</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state illustration="no-applications" title="Belum ada proyek"
                          description="Proyek dibuat otomatis ketika Anda menerima lamaran mahasiswa.">
            <x-slot:action>
                <x-ui.button variant="secondary" :href="route('institution.applications.index')">Tinjau lamaran</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @endif
</div>
@endsection
