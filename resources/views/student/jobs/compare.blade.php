{{-- bandingkan 2-3 lowongan berdampingan. pola tabel sama dengan bandingkan talenta (x-ui.table + .sg-compare, audit F-23);
     perilaku tetap: 2-3 id dari ?ids=, kriteria sama. nilai kosong ditampilkan "—", tidak dikarang. --}}
@extends('layouts.app')

@section('title', 'Bandingkan Lowongan')

@section('content')
@php
    $tanggal = fn ($date) => $date ? $date->locale('id')->translatedFormat('j M Y') : null;
    $relatif = fn ($date) => $date ? $date->locale('id')->diffForHumans() : null;
    // manfaat disimpan sebagai teks per baris ("- item"), bukan array
    $manfaat = function ($job) {
        $items = is_array($job->benefits) ? $job->benefits : preg_split('/\R/', (string) $job->benefits);

        return array_values(array_filter(array_map(fn ($b) => trim(ltrim(trim((string) $b), '-•* ')), $items)));
    };
@endphp

<div class="sg-container sg-compare">
    <x-ui.page-header title="Bandingkan Lowongan" :description="'Membandingkan ' . $jobs->count() . ' lowongan berdampingan.'"
                      :back="route('student.jobs.index')" back-label="Kembali ke lowongan" />

    <x-ui.table caption="Perbandingan lowongan" :stack="false" class="sg-compare__table">
        <x-slot:head>
            <th scope="col">Kriteria</th>
            @foreach ($jobs as $job)
                <th scope="col">
                    <span class="sg-compare__person">
                        <x-ui.avatar :src="$job->company?->logo_url" :name="$job->company?->name ?? 'Perusahaan'" square decorative />
                        <span>{{ $job->title }}<span class="sg-table__meta">{{ $job->company?->name ?? '—' }}</span></span>
                    </span>
                </th>
            @endforeach
        </x-slot:head>

        <tr>
            <th scope="row">Jenis</th>
            @foreach ($jobs as $job)
                <td>{{ job_type_label($job->job_type) ?? '—' }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Lokasi</th>
            @foreach ($jobs as $job)
                <td>{{ $job->location ?: '—' }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Gaji</th>
            @foreach ($jobs as $job)
                <td>{{ job_salary_label($job) ?? 'Tidak disebutkan' }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Pengalaman</th>
            @foreach ($jobs as $job)
                <td>{{ $job->experience_level ?: '—' }}</td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Keahlian</th>
            @foreach ($jobs as $job)
                @php($skills = collect((array) $job->skills)->filter(fn ($s) => filled($s)))
                <td>
                    @if ($skills->isNotEmpty())
                        <ul class="sg-compare__chips">
                            @foreach ($skills as $skill)
                                <li><x-ui.chip>{{ $skill }}</x-ui.chip></li>
                            @endforeach
                        </ul>
                    @else
                        —
                    @endif
                </td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Manfaat</th>
            @foreach ($jobs as $job)
                @php($items = $manfaat($job))
                <td>
                    @if (count($items) > 0)
                        <ul class="sg-compare__list">
                            @foreach (array_slice($items, 0, 5) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        —
                    @endif
                </td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Tenggat</th>
            @foreach ($jobs as $job)
                <td class="tabular-nums">
                    @if ($job->expires_at)
                        {{ $tanggal($job->expires_at) }}<span class="sg-table__meta">{{ $relatif($job->expires_at) }}</span>
                    @else
                        Tidak ada batas
                    @endif
                </td>
            @endforeach
        </tr>
        <tr>
            <th scope="row">Aksi</th>
            @foreach ($jobs as $job)
                <td>
                    <x-ui.button variant="secondary" :href="route('student.jobs.show', $job->id)">
                        Lihat detail<span class="sr-only"> {{ $job->title }}</span>
                    </x-ui.button>
                </td>
            @endforeach
        </tr>
    </x-ui.table>
</div>
@endsection
