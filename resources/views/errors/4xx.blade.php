{{-- phase 26: 4xx di dalam shell aplikasi (navbar + footer). status http tidak diubah oleh view ini. --}}
@extends('layouts.app')

@section('title', 'Permintaan tidak dapat diproses')

@section('content')
    @include('errors.partials.content', [
        'code' => $exception->getStatusCode(),
        'title' => 'Permintaan tidak dapat diproses',
        'description' => 'Permintaan ini tidak dapat kami proses. Kembali ke halaman sebelumnya atau mulai lagi dari beranda.',
    ])
@endsection
