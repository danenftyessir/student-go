<!DOCTYPE html>
<html lang="id">
<head>
    {{-- phase 6: satu template untuk masuk, pilih peran, tiga registrasi, lupa/reset password dan verifikasi email.
         head bersama (judul "… — Student Go", favicon, vite app.css + app.js, alpine). --}}
    @include('partials.head')
    @stack('styles')
</head>
<body class="font-sans antialiased sg-auth-body">
    <a href="#main" class="sg-skip">Lewati ke konten utama</a>

    {{-- kepala ringkas: wordmark ke beranda + tautan umum (pengganti "Back to Home / Contact / About" lama) --}}
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

    <div class="sg-auth @hasSection('aside') sg-auth--split @endif">
        <main id="main" tabindex="-1" class="sg-auth__main">
            <div class="sg-auth__body {{ trim($__env->yieldContent('auth_width')) === 'wide' ? 'sg-auth__body--wide' : '' }}">
                {{-- pesan flash: kunci session sama seperti sebelumnya (success, error, info, warning, status) --}}
                @php
                    $sgFlash = array_filter([
                        'success' => session('success') ?: session('status'),
                        'danger' => session('error'),
                        'info' => session('info'),
                        'warning' => session('warning'),
                    ]);
                @endphp
                @foreach ($sgFlash as $tone => $message)
                    <x-ui.alert :tone="$tone" class="sg-auth__flash">{{ $message }}</x-ui.alert>
                @endforeach

                @yield('content')
            </div>
        </main>

        @hasSection('aside')
            {{-- panel samping (>= 1024px): wordmark + motif kontur, tanpa foto dan tanpa angka (D-19, J9) --}}
            <aside class="sg-auth__aside sg-contour" aria-label="Tentang Student Go">
                <div class="sg-auth__aside-inner">
                    @yield('aside')
                </div>
            </aside>
        @endif
    </div>

    @stack('scripts')
</body>
</html>
