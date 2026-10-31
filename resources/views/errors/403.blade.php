{{-- phase 26: 403 di dalam shell aplikasi. pesan abort(403) dari middleware ditampilkan tanpa awalan "Unauthorized."
     (pesannya ditulis di aplikasi, bukan detail teknis); pesan bawaan berbahasa inggris diganti teks umum. status tetap 403. --}}
@php
    $sgForbidden = trim(preg_replace('/^Unauthorized\.\s*/i', '', (string) $exception->getMessage()));
    if ($sgForbidden === '' || preg_match('/unauthori[sz]ed|forbidden|[\\\[]/i', $sgForbidden)) {
        $sgForbidden = 'Akun Anda tidak memiliki izin untuk membuka halaman ini.';
    }
@endphp
@extends('layouts.app')

@section('title', 'Akses tidak diizinkan')

@section('content')
    @include('errors.partials.content', [
        'code' => 403,
        'title' => 'Akses tidak diizinkan',
        'description' => $sgForbidden,
    ])
@endsection
