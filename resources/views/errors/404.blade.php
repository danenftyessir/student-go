{{-- phase 26: 404 di dalam shell aplikasi (navbar + footer). status http tidak diubah oleh view ini. --}}
@extends('layouts.app')

@section('title', 'Halaman tidak ditemukan')

@section('content')
    @include('errors.partials.content', [
        'code' => 404,
        'title' => 'Halaman tidak ditemukan',
        'description' => 'Alamat yang Anda buka tidak ada, sudah dipindahkan, atau datanya sudah dihapus. Periksa kembali alamatnya atau mulai lagi dari beranda.',
    ])
@endsection
