<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    @stack('styles')
</head>
<body class="font-sans antialiased">
    <a href="#main" class="sg-skip">Lewati ke konten utama</a>

    {{-- kolom flex: main mengisi sisa tinggi sehingga footer menempel di bawah halaman pendek (phase 29 F, C4) --}}
    <div class="sg-shell bg-gray-50">
        @include('components.navbar')

        <main id="main" tabindex="-1" class="sg-main">
            @yield('content')
        </main>

        @include('components.footer')
    </div>

    {{-- kembali ke atas: muncul setelah menggulir 300px --}}
    <button type="button" id="scroll-to-top" class="sg-btn sg-btn--secondary sg-btn--icon sg-scrolltop" aria-label="Kembali ke atas" hidden>
        <x-icon name="chevron-up" />
    </button>

    {{-- toast (flash session + window.showNotification) dan dialog konfirmasi bersama --}}
    <div id="notification-container" class="sg-toasts" aria-live="polite"></div>
    @php
        // kunci flash session yang sama seperti sebelumnya
        $sgFlash = array_filter([
            'success' => session('success'),
            'error' => session('error'),
            'info' => session('info'),
            'warning' => session('warning'),
        ]);
    @endphp
    <script type="application/json" id="sg-flash">@json($sgFlash)</script>
    <x-ui.confirm-dialog />

    @stack('scripts')
</body>
</html>
