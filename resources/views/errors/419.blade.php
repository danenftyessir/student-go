{{-- phase 26: 419 di dalam shell aplikasi (navbar + footer). status http tidak diubah oleh view ini. --}}
@extends('layouts.app')

@section('title', 'Sesi Anda sudah berakhir')

@section('content')
    @include('errors.partials.content', [
        'code' => 419,
        'title' => 'Sesi Anda sudah berakhir',
        'description' => 'Halaman ini terlalu lama terbuka sehingga formulir tidak dapat dikirim. Kembali ke halaman sebelumnya, muat ulang, lalu kirim formulir sekali lagi.',
    ])
@endsection
