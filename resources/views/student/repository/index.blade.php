{{-- resources/views/student/repository/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Repositori Pengetahuan')

{{-- phase 14: repositori sebagai perpustakaan. satu form GET (nama parameter tetap: search, category, province_id, year,
     sort, page), tabel enam kolom, tanpa hero foto dan tanpa baris unggulan yang menduplikasi daftar.
     urutan dan hitungan dari controller (phase 1, D-05): header = semua dokumen yang bisa dicapai, "N dari M" bila difilter. --}}

@use('App\Support\RepositoryFormat', 'Fmt')

@php
    $indexUrl = route('student.repository.index');
    $sdgOptions = collect(range(1, 17))->mapWithKeys(fn ($n) => [$n => 'SDG ' . $n . ' · ' . sdg_label($n)])->all();
    $provinceOptions = $provinces->pluck('name', 'id')->all();
    $yearOptions = collect($years)->map(fn ($y) => (int) $y)->mapWithKeys(fn ($y) => [$y => (string) $y])->all();
    // hanya nilai urutan yang dikenali controller
    $sorts = ['latest' => 'Terbaru', 'oldest' => 'Terlama', 'popular' => 'Paling banyak diunduh', 'most_viewed' => 'Paling banyak dilihat', 'title' => 'Judul (A–Z)'];
    $currentSort = array_key_exists(request('sort'), $sorts) ? request('sort') : 'latest';
    $category = request('category');
    $category = is_array($category) ? ($category[0] ?? null) : $category;

    $activeCount = collect(['category', 'province_id', 'year'])->filter(fn ($k) => filled(request($k)))->count();
    // filter yang dikenali controller (regency_id dan file_type tetap berlaku bila ada di URL)
    $isFiltered = collect(['search', 'category', 'province_id', 'regency_id', 'year', 'file_type'])->contains(fn ($k) => filled(request($k)));
@endphp

@section('content')
<div class="sg-container sg-repo">
    <x-ui.page-header
        title="Repositori Pengetahuan"
        description="Laporan dan dokumentasi proyek KKN yang sudah selesai. Cari menurut judul, penulis atau kata kunci, lalu saring menurut SDG, provinsi dan tahun.">
        <x-slot:actions>
            <a href="{{ route('student.chatbot.index') }}" class="sg-btn sg-btn--secondary">
                <x-icon name="message-square-text" size="sm" />Tanya SAGO
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar
        :action="$indexUrl"
        search="search"
        search-label="Cari dokumen"
        search-placeholder="Cari judul, deskripsi, penulis atau tag"
        :reset-href="$isFiltered || request()->filled('sort') ? $indexUrl : null"
        :active="$activeCount"
        id="repo-filter"
        class="sg-repo__filters">
        <x-ui.field label="SDG" for="filter-category" class="sg-repo__sdg">
            <x-ui.select name="category" id="filter-category" :options="$sdgOptions" :selected="$category" placeholder="Semua SDG" />
        </x-ui.field>
        <x-ui.field label="Provinsi" for="filter-province">
            <x-ui.select name="province_id" id="filter-province" :options="$provinceOptions" :selected="request('province_id')" placeholder="Semua provinsi" />
        </x-ui.field>
        <x-ui.field label="Tahun diunggah" for="filter-year">
            <x-ui.select name="year" id="filter-year" :options="$yearOptions" :selected="request('year')" placeholder="Semua tahun" />
        </x-ui.field>
        <x-ui.field label="Urutkan" for="filter-sort">
            <x-ui.select name="sort" id="filter-sort" :options="$sorts" :selected="$currentSort" />
        </x-ui.field>
    </x-ui.filter-bar>

    <p class="sg-repo__count" aria-live="polite">
        @if ($isFiltered)
            <b>{{ Fmt::number($totalDocuments) }}</b> dari {{ Fmt::number($stats['total_documents']) }} dokumen
        @else
            <b>{{ Fmt::number($stats['total_documents']) }}</b> dokumen
        @endif
    </p>

    @if ($documents->count() > 0)
        <x-ui.table caption="Daftar dokumen repositori" class="sg-repo-table-wrap">
            <x-slot:head>
                <th scope="col">Judul</th>
                <th scope="col">Instansi</th>
                <th scope="col">Wilayah</th>
                <th scope="col" class="sg-num">Tahun</th>
                <th scope="col" class="sg-num">Dilihat</th>
                <th scope="col" class="sg-num">Diunduh</th>
            </x-slot:head>
            @foreach ($documents as $document)
                @php
                    $file = Fmt::fileChip($document->file_type, $document->file_size);
                    $sdgs = Fmt::sdgs($document->categories);
                @endphp
                <tr data-document-id="{{ $document->id }}">
                    <td data-label="Judul">
                        <div class="sg-repo-row">
                            <a href="{{ route('student.repository.show', $document->id) }}" class="sg-repo-row__title sg-table__primary">{{ $document->title }}</a>
                            @if ($file || count($sdgs))
                                <ul class="sg-repo-row__meta" aria-label="Format dan SDG">
                                    @if ($file)
                                        <li><x-ui.chip icon="file-text" class="sg-repo-file">{{ $file }}</x-ui.chip></li>
                                    @endif
                                    @foreach ($sdgs as $sdg)
                                        <li><span class="sg-chip sg-sdg" data-sdg="{{ $sdg['id'] }}"><span class="sg-chip__swatch" aria-hidden="true"></span>SDG {{ $sdg['id'] }} · {{ $sdg['name'] }}</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </td>
                    <td data-label="Instansi"><span>{{ $document->institution_name ?: '—' }}</span></td>
                    <td data-label="Wilayah">
                        <span>{{ optional($document->regency)->name ?? optional($document->province)->name ?? '—' }}@if ($document->regency && $document->province)<span class="sg-table__meta">{{ $document->province->name }}</span>@endif</span>
                    </td>
                    <td data-label="Tahun" class="sg-num"><span>{{ $document->year ?: '—' }}</span></td>
                    <td data-label="Dilihat" class="sg-num"><span>{{ Fmt::number($document->view_count) }}</span></td>
                    <td data-label="Diunduh" class="sg-num"><span>{{ Fmt::number($document->download_count) }}</span></td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.pagination :paginator="$documents" label="Halaman repositori" class="mt-6" />
    @elseif ($isFiltered)
        <x-ui.empty-state
            title="Tidak ada dokumen yang cocok"
            description="Coba kata kunci lain atau hapus sebagian filter."
            illustration="no-search-results">
            <x-slot:action>
                <a href="{{ $indexUrl }}" class="sg-btn sg-btn--secondary">Hapus semua filter</a>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state
            title="Belum ada dokumen"
            description="Dokumen proyek KKN yang sudah disetujui akan muncul di sini."
            illustration="no-documents" />
    @endif
</div>
@endsection
