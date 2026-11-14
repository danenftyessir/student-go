{{-- halaman publik verifikasi proyek KKN (masukan juri, 2026-11-14): bukti bahwa proyek selesai dan divalidasi instansi.
     dibuka lewat kode dari portofolio; tanpa email/telepon (D-04). semua nilai dari data proyek, tidak ada yang dikarang. --}}
@extends('layouts.app')

@section('title', 'Verifikasi Proyek KKN')

@section('content')
@php
    $student = $project->student;
    $problem = $project->problem;
    $institution = $problem?->institution;
    $review = $project->institutionReview;
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j F Y') : null;
    $periode = collect([$tanggal($project->actual_start_date ?? $project->start_date), $tanggal($project->actual_end_date ?? $project->end_date)])->filter()->implode(' – ');
    $lokasi = collect([$problem?->regency?->name, $problem?->province?->name])->filter()->implode(', ');
    $nilai = fn ($v) => $v ? number_format((float) $v, 1, ',', '.').' dari 5' : null;
@endphp

<div class="sg-container sg-verify">
    <x-ui.page-header title="Verifikasi proyek KKN" description="Bukti bahwa proyek di bawah ini tercatat di Student Go, selesai, dan divalidasi oleh instansi pemiliknya." />

    <x-ui.alert tone="success" title="Terverifikasi">
        Proyek ini berstatus selesai di Student Go{{ $institution ? ' dan divalidasi oleh '.$institution->name : '' }}.
    </x-ui.alert>

    <section class="sg-verify__section" aria-labelledby="verify-project">
        <h2 id="verify-project">{{ $problem->title ?? $project->title }}</h2>
        <x-ui.dl :items="[
            'Mahasiswa' => $student?->display_name,
            'Universitas' => $student?->university?->name,
            'Instansi yang memvalidasi' => $institution?->name,
            'Lokasi' => $lokasi,
            'Periode pelaksanaan' => $periode,
            'Peran' => $project->role_in_team,
            'Nomor sertifikat' => $project->certificate_number,
            'Kode verifikasi' => $kode,
        ]" />
        @if ($student?->user?->username)
            <p class="sg-verify__link"><a href="{{ route('profile.public', $student->user->username) }}" class="sg-inst-link">Lihat portofolio {{ $student->display_name }}</a></p>
        @endif
    </section>

    @if ($review)
        <section class="sg-verify__section" aria-labelledby="verify-review">
            <h2 id="verify-review">Ulasan instansi</h2>
            <x-ui.dl :items="[
                'Nilai keseluruhan' => $nilai($review->rating),
                'Profesionalisme' => $nilai($review->professionalism_rating),
                'Komunikasi' => $nilai($review->communication_rating),
                'Kualitas hasil' => $nilai($review->quality_rating),
                'Ketepatan waktu' => $nilai($review->timeliness_rating),
            ]" />
            @if (filled($review->comment))
                <blockquote class="sg-verify__quote">{{ $review->comment }}</blockquote>
            @endif
        </section>
    @endif

    <p class="sg-home-note">Diambil langsung dari data Student Go saat halaman ini dibuka.</p>
    @if (config('app.demo_data'))<p class="sg-demo-note">Data uji coba (demo), bukan data pengguna sebenarnya.</p>@endif
</div>
@endsection
