@extends('layouts.app')

@section('title', 'Temukan Koneksi')

{{-- phase 13: direktori mahasiswa. satu form GET (nama parameter tetap: search, university_id, major, skills, page),
     daftar bergaris pemisah, satu aksi per baris, tanpa foto hero. nama dan inisial dari Student::display_name (D-03);
     kontak tidak pernah tampil di daftar (D-04). status pertemanan dari Student::friendshipStatusWith():
     none | request_sent | request_received | friends. --}}

@php
    $searchUrl = route('student.friends.search');
    $universityOptions = \App\Models\University::orderBy('name')->pluck('name', 'id')->all();
    $skillsValue = request('skills');
    $skillsValue = is_array($skillsValue) ? implode(', ', $skillsValue) : $skillsValue;

    $activeCount = collect(['university_id', 'major', 'skills'])->filter(fn ($k) => filled(request($k)))->count();
    $isFiltered = $activeCount > 0 || filled(request('search'));
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');

    // filter cepat: tautan yang sama seperti sebelumnya (satu parameter per tautan)
    $quickFilters = [];
    if (filled($student->university_id)) {
        $quickFilters[] = [
            'label' => 'Dari Universitas Saya',
            'href' => route('student.friends.search', ['university_id' => $student->university_id]),
            'active' => (string) request('university_id') === (string) $student->university_id,
        ];
    }
    if (filled($student->major)) {
        $quickFilters[] = [
            'label' => 'Jurusan yang Sama',
            'href' => route('student.friends.search', ['major' => $student->major]),
            'active' => (string) request('major') === (string) $student->major,
        ];
    }
@endphp

@section('content')
<div class="sg-container pb-16">
    <x-ui.page-header
        title="Temukan Koneksi"
        description="Cari mahasiswa KKN menurut nama, universitas, jurusan atau keterampilan, lalu kirim permintaan koneksi.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="users" :href="route('student.friends.index')">Jaringan Anda</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar
        :action="$searchUrl"
        search="search"
        search-label="Cari mahasiswa"
        search-placeholder="Cari nama atau nama pengguna"
        :reset-href="$isFiltered ? $searchUrl : null"
        :active="$activeCount"
        id="friends-filter">
        <x-ui.field label="Universitas" for="filter-university">
            <x-ui.select name="university_id" id="filter-university" :options="$universityOptions"
                         :selected="request('university_id')" placeholder="Semua universitas" />
        </x-ui.field>
        <x-ui.field label="Jurusan" for="filter-major">
            <x-ui.input name="major" id="filter-major" :value="request('major')" placeholder="Contoh: Teknik Informatika" />
        </x-ui.field>
        <x-ui.field label="Keterampilan" for="filter-skills">
            <x-ui.input name="skills" id="filter-skills" :value="$skillsValue" placeholder="Contoh: Web Development" />
        </x-ui.field>
    </x-ui.filter-bar>

    @if (count($quickFilters))
        <nav class="mt-4 flex flex-wrap items-center gap-2" aria-label="Filter cepat">
            <span class="text-sm text-gray-600">Filter cepat:</span>
            @foreach ($quickFilters as $quick)
                <a href="{{ $quick['href'] }}" @class(['sg-btn', 'sg-btn--secondary' => ! $quick['active'], 'sg-btn--tertiary' => $quick['active']])
                   @if ($quick['active']) aria-current="true" @endif>
                    @if ($quick['active'])<x-icon name="check" size="sm" />@endif{{ $quick['label'] }}
                </a>
            @endforeach
        </nav>
    @endif

    <section class="mt-6" aria-labelledby="judul-hasil">
        <h2 id="judul-hasil" class="sr-only">Hasil pencarian</h2>
        <p class="m-0 mb-3 text-sm text-gray-700 tabular-nums" aria-live="polite" data-result-count="{{ $results->total() }}">
            <b class="font-semibold text-gray-900">{{ $fmt($results->total()) }}</b> mahasiswa ditemukan
            @if ($isFiltered)<span class="text-gray-600">· sesuai filter</span>@endif
        </p>

        @if ($results->count() > 0)
            <x-ui.list label="Daftar mahasiswa">
                @foreach ($results as $result)
                    @php
                        $name = $result->display_name;
                        $meta = collect([$result->major, $result->university->name ?? null])->filter()->implode(' · ');
                    @endphp
                    <x-ui.list-item :title="$name" :href="route('student.friends.profile', $result->id)" :meta="$meta"
                                    data-student-id="{{ $result->id }}" data-friendship="{{ $result->friendship_status }}">
                        <x-slot:leading>
                            <x-ui.avatar :src="$result->profile_photo_path ? $result->profile_photo_url : null" :name="$name" />
                        </x-slot:leading>
                        <x-slot:end>
                            @switch($result->friendship_status)
                                @case('friends')
                                    <x-ui.status domain="friend" value="accepted" />
                                    @break
                                @case('request_sent')
                                    <x-ui.status domain="friend" value="pending" />
                                    @break
                                @case('request_received')
                                    <x-ui.button variant="secondary" icon="user-check" :href="route('student.friends.index')" aria-label="Tanggapi permintaan dari {{ $name }}">Tanggapi</x-ui.button>
                                    @break
                                @default
                                    <form method="POST" action="{{ route('student.friends.send-request', $result->id) }}">
                                        @csrf
                                        <x-ui.button type="submit" variant="secondary" icon="user-plus" aria-label="Hubungkan dengan {{ $name }}">Hubungkan</x-ui.button>
                                    </form>
                            @endswitch
                        </x-slot:end>
                    </x-ui.list-item>
                @endforeach
            </x-ui.list>

            <x-ui.pagination :paginator="$results" label="Halaman hasil pencarian" class="mt-6" />
        @else
            <x-ui.empty-state illustration="no-search-results" title="Tidak ada mahasiswa yang cocok"
                              description="Coba kata kunci lain atau longgarkan filter universitas, jurusan dan keterampilan.">
                @if ($isFiltered)
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="$searchUrl">Atur ulang pencarian</x-ui.button>
                    </x-slot:action>
                @endif
            </x-ui.empty-state>
        @endif
    </section>
</div>
@endsection
