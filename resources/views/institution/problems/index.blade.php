@extends('layouts.app')

@section('title', 'Kelola Masalah')

@section('content')
@php
    // phase 18: daftar masalah sebagai tabel (Judul · Status · Pelamar · Kuota · Tenggat · Diperbarui · Aksi).
    // filter lama tetap: search, status, sort (latest | oldest | most_applied); angka hanya dari query.
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : '—';
    $statusOptions = collect(\App\Support\StatusVocabulary::values('problem'))->map(fn ($entry) => $entry[0])->all();
    $filtered = request()->filled('search') || request()->filled('status');
    $activeFilters = (int) request()->filled('status') + (int) (request()->filled('sort') && request('sort') !== 'latest');
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Masalah" description="Kelola masalah KKN yang diterbitkan instansi Anda.">
        <x-slot:actions>
            <x-ui.button :href="route('institution.problems.create')" icon="plus">Buat masalah baru</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($stats['total'] > 0)
        <x-ui.summary label="Ringkasan masalah" class="sg-inst-page__summary" :items="[
            ['value' => $stats['total'], 'label' => 'masalah'],
            ['value' => $stats['draft'] ?: null, 'label' => 'draf'],
            ['value' => $stats['open'] ?: null, 'label' => 'dibuka'],
            ['value' => $stats['in_progress'] ?: null, 'label' => 'berjalan'],
            ['value' => $stats['completed'] ?: null, 'label' => 'selesai'],
            ['value' => $stats['closed'] ?: null, 'label' => 'ditutup'],
        ]" />

        <x-ui.filter-bar :action="route('institution.problems.index')" search-label="Cari masalah" search-placeholder="Cari judul atau deskripsi"
                         :reset-href="route('institution.problems.index')" :active="$activeFilters" id="filter-masalah" class="sg-inst-page__filters">
            <x-ui.field label="Status" for="status">
                <x-ui.select name="status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" />
            </x-ui.field>
            <x-ui.field label="Urutkan" for="sort">
                <x-ui.select name="sort" :options="['latest' => 'Terbaru', 'oldest' => 'Terlama', 'most_applied' => 'Paling banyak pelamar']" :selected="request('sort', 'latest')" />
            </x-ui.field>
        </x-ui.filter-bar>
    @endif

    @if ($problems->count() > 0)
        <x-ui.table caption="Daftar masalah instansi">
            <x-slot:head>
                <th scope="col">Judul</th>
                <th scope="col">Status</th>
                <th scope="col" class="sg-num">Pelamar</th>
                <th scope="col" class="sg-num">Kuota</th>
                <th scope="col">Tenggat</th>
                <th scope="col">Diperbarui</th>
                <th scope="col">Aksi</th>
            </x-slot:head>
            @foreach ($problems as $problem)
                @php
                    $deadlinePassed = $problem->application_deadline && $problem->application_deadline->isPast() && $problem->status === 'open';
                @endphp
                <tr>
                    <td data-label="Judul">
                        <a href="{{ route('institution.problems.show', $problem->id) }}" class="sg-table__primary">{{ $problem->title }}</a>
                        <span class="sg-table__meta">{{ collect([$problem->regency?->name, $problem->province?->name])->filter()->implode(', ') }}</span>
                    </td>
                    <td data-label="Status"><x-ui.status domain="problem" :value="$problem->status" /></td>
                    <td data-label="Pelamar" class="sg-num">
                        @if ($problem->applications_count > 0)
                            <a href="{{ route('institution.applications.index', ['problem_id' => $problem->id]) }}">{{ $problem->applications_count }}<span class="sr-only"> pelamar untuk {{ $problem->title }}</span></a>
                        @else
                            0
                        @endif
                    </td>
                    <td data-label="Kuota" class="sg-num">
                        <span>{{ $problem->required_students }}</span>
                        <span class="sg-table__meta">{{ $problem->accepted_applications_count }} diterima</span>
                    </td>
                    <td data-label="Tenggat">
                        <span>{{ $tanggal($problem->application_deadline) }}</span>
                        @if ($deadlinePassed)<span class="sg-table__meta sg-inst-warn">Tenggat lewat</span>@endif
                    </td>
                    <td data-label="Diperbarui">{{ $tanggal($problem->updated_at) }}</td>
                    <td data-label="Aksi">
                        <a href="{{ route('institution.problems.edit', $problem->id) }}">Ubah<span class="sr-only"> {{ $problem->title }}</span></a>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.pagination :paginator="$problems" label="Halaman daftar masalah" class="sg-inst-page__pagination" />
    @elseif ($filtered)
        <x-ui.empty-state illustration="no-search-results" title="Tidak ada masalah yang cocok"
                          description="Ubah kata kunci atau status, atau tampilkan semua masalah.">
            <x-slot:action>
                <x-ui.button variant="secondary" :href="route('institution.problems.index')">Tampilkan semua masalah</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state title="Belum ada masalah"
                          description="Terbitkan masalah KKN agar mahasiswa dapat menemukan dan melamarnya. Mulai dengan tombol Buat masalah baru; Anda bisa menyimpannya sebagai draf lebih dulu." />
    @endif
</div>
@endsection
