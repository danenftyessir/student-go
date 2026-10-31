{{-- phase 26: templat mandiri untuk 500/503/5xx. tidak memakai layouts.app (navbar membaca auth, session memakai database)
     sehingga tetap tampil saat database mati: tanpa csrf, auth, session, query atau bundel js. --}}
@php
    // css bundel saja; jika manifest vite tidak ada, halaman tetap terbaca sebagai html polos
    try {
        $sgErrorCss = app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css'])->toHtml();
    } catch (\Throwable $e) {
        $sgErrorCss = '';
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — Student Go</title>
    <link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('assets/brand/favicon.ico') }}" sizes="32x32">
    {!! $sgErrorCss !!}
</head>
<body class="font-sans antialiased sg-error-page">
    <a href="#main" class="sg-skip">Lewati ke konten utama</a>

    {{-- kepala ringkas yang sama dengan halaman auth --}}
    <header class="sg-auth-header">
        <div class="sg-auth-header__bar">
            <a href="{{ route('home') }}" class="sg-auth-header__brand" aria-label="Student Go, ke beranda">
                <img src="{{ asset('assets/brand/student-go-logo.svg') }}" alt="" width="202" height="40">
            </a>
            <nav class="sg-auth-header__nav" aria-label="Tautan umum">
                <a href="{{ route('about') }}">Tentang Kami</a>
                <a href="{{ route('contact') }}">Kontak</a>
            </nav>
        </div>
    </header>

    <main id="main" tabindex="-1" class="sg-main">
        @include('errors.partials.content')
    </main>
</body>
</html>
