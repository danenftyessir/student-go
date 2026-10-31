@extends('layouts.auth')

{{-- phase 6: pilih peran. tiga baris bertepi (ikon, nama peran, tugas utama), tujuan sama:
     register.student, register.institution, register.company. --}}

@section('title', 'Daftar')
@section('auth_width', 'wide')

@section('content')
    <x-ui.page-header title="Buat akun Student Go" description="Pilih peran Anda. Setiap peran punya formulir pendaftaran dan ruang kerja sendiri." />

    @php
        $roles = [
            ['register.student', 'graduation-cap', 'Mahasiswa', 'Cari masalah KKN dari instansi, kirim lamaran, dan bangun portofolio dari proyek yang selesai.'],
            ['register.institution', 'map-pin', 'Instansi', 'Publikasikan masalah di wilayah Anda dan tinjau mahasiswa yang melamar. Perlu dokumen verifikasi.'],
            ['register.company', 'briefcase', 'Perusahaan', 'Telusuri talenta mahasiswa, terbitkan lowongan, dan kelola lamaran yang masuk.'],
        ];
    @endphp

    <ul class="sg-role-list" aria-label="Jenis akun">
        @foreach ($roles as [$route, $icon, $name, $desc])
            <li>
                <a href="{{ route($route) }}" class="sg-role-row">
                    <span class="sg-role-row__icon"><x-icon :name="$icon" /></span>
                    <span>
                        <span class="sg-role-row__name">Daftar sebagai {{ $name }}</span>
                        <span class="sg-role-row__desc">{{ $desc }}</span>
                    </span>
                    <x-icon name="chevron-right" class="sg-role-row__chev" />
                </a>
            </li>
        @endforeach
    </ul>

    <p class="sg-auth-alt">Sudah punya akun? <a href="{{ route('login') }}" class="sg-auth-link">Masuk</a></p>
@endsection
