@extends('layouts.app')

@section('title', 'Lowongan')

@section('content')
@php
    // phase 23: header + tab status + filter berlabel + tabel 7 kolom (judul, kategori, status, pelamar, dilihat, tanggal, aksi).
    // parameter lama tetap: ?search=, ?status=, ?category=, ?page=. angka hanya dari query.
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : '—';
    $total = array_sum($statusCounts);
    $tabUrl = fn (?string $status) => route('company.jobs.index', array_filter([
        'status' => $status, 'search' => $filters['search'] ?: null, 'category' => $filters['category'],
    ]));
    $tabs = [['label' => 'Semua', 'href' => $tabUrl(null), 'active' => $filters['status'] === '', 'count' => $total]];
    foreach (['posted', 'draft', 'closed', 'archived'] as $value) {
        if (($statusCounts[$value] ?? 0) > 0 || $filters['status'] === $value) {
            $tabs[] = ['label' => \App\Support\StatusVocabulary::label('job_posting', $value), 'href' => $tabUrl($value),
                       'active' => $filters['status'] === $value, 'count' => $statusCounts[$value] ?? 0];
        }
    }
    $isFiltered = $filters['search'] !== '' || $filters['category'];
@endphp

<div class="sg-container sg-co-jobs">
    <x-ui.page-header title="Lowongan" description="Kelola lowongan magang dan kerja perusahaan Anda serta pantau pelamarnya.">
        <x-slot:actions>
            <x-ui.button :href="route('company.jobs.create')" icon="plus">Pasang lowongan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($total > 0)
        <x-ui.summary label="Ringkasan lowongan" class="sg-co-jobs__summary" :items="[
            ['value' => $total, 'label' => 'lowongan'],
            ['value' => ($statusCounts['posted'] ?? 0) ?: null, 'label' => 'dibuka'],
            ['value' => $totalApplicants ?: null, 'label' => 'pelamar'],
        ]" />

        <x-ui.tabs :items="$tabs" label="Status lowongan" class="sg-co-jobs__tabs" />

        <x-ui.filter-bar :action="route('company.jobs.index')" search-label="Cari lowongan" search-placeholder="Judul atau kategori"
                         :reset-href="route('company.jobs.index', array_filter(['status' => $filters['status'] ?: null]))"
                         :active="(int) (bool) $filters['category']" id="filter-lowongan" class="sg-co-jobs__filters">
            <x-ui.field label="Kategori" for="category">
                <x-ui.select name="category" :options="$categories->pluck('name', 'id')->all()" :selected="$filters['category']" placeholder="Semua kategori" />
            </x-ui.field>
            @if ($filters['status'] !== '')
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
        </x-ui.filter-bar>
    @endif

    <x-ui.table caption="Daftar lowongan perusahaan" class="sg-co-jobs__table">
        <x-slot:head>
            <th scope="col">Judul</th>
            <th scope="col">Kategori</th>
            <th scope="col">Status</th>
            <th scope="col" class="sg-num">Pelamar</th>
            <th scope="col" class="sg-num">Dilihat</th>
            <th scope="col">Tanggal dibuat</th>
            <th scope="col">Aksi</th>
        </x-slot:head>

        @forelse ($jobPostings as $job)
            <tr data-job-id="{{ $job->id }}">
                <td data-label="Judul">
                    <a href="{{ route('company.jobs.show', $job->id) }}" class="sg-table__primary">{{ $job->title }}</a>
                    @if ($job->location)<span class="sg-table__meta">{{ $job->location }}</span>@endif
                </td>
                <td data-label="Kategori">{{ $job->jobCategory->name ?? '—' }}</td>
                <td data-label="Status"><x-ui.status domain="job_posting" :value="$job->status" /></td>
                <td data-label="Pelamar" class="sg-num">
                    @if ($job->job_applications_count > 0)
                        <a href="{{ route('company.applications.index', ['job_posting_id' => $job->id]) }}">{{ $fmt($job->job_applications_count) }}</a>
                    @else
                        0
                    @endif
                </td>
                <td data-label="Dilihat" class="sg-num">{{ $fmt($job->views_count) }}</td>
                <td data-label="Tanggal dibuat">{{ $tanggal($job->created_at) }}</td>
                <td data-label="Aksi">
                    <span class="sg-co-jobs__actions">
                        <x-ui.icon-button icon="eye" :label="'Lihat ' . $job->title" :href="route('company.jobs.show', $job->id)" />
                        <x-ui.icon-button icon="pencil" :label="'Ubah ' . $job->title" :href="route('company.jobs.edit', $job->id)" />
                        <form method="POST" action="{{ route('company.jobs.destroy', $job->id) }}" class="sg-co-jobs__delete"
                              data-sg-confirm="Lowongan '{{ $job->title }}' akan dihapus dari daftar lowongan."
                              data-sg-confirm-title="Hapus lowongan?" data-sg-confirm-label="Hapus">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sg-icon-btn sg-co-jobs__danger" aria-label="Hapus {{ $job->title }}"><x-icon name="trash-2" /></button>
                        </form>
                    </span>
                </td>
            </tr>
        @empty
            {{-- keadaan kosong di dalam tabel (pola yang dipertahankan audit) --}}
            <tr class="sg-co-jobs__empty-row">
                <td colspan="7">
                    @if ($total === 0)
                        <x-ui.empty-state title="Belum ada lowongan" illustration="no-applications"
                                          description="Pasang lowongan magang atau kerja pertama Anda; mahasiswa dapat melamar setelah lowongan dibuka.">
                            <x-slot:action>
                                <x-ui.button variant="secondary" :href="route('company.jobs.create')">Pasang lowongan pertama</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state title="Tidak ada lowongan yang cocok" illustration="no-search-results"
                                          description="Tidak ada lowongan dengan status, kata kunci atau kategori ini.">
                            <x-slot:action>
                                <x-ui.button variant="secondary" :href="route('company.jobs.index')">Tampilkan semua lowongan</x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
        @endforelse
    </x-ui.table>

    <x-ui.pagination :paginator="$jobPostings" label="Halaman lowongan" class="sg-co-jobs__pager" />
</div>
@endsection
