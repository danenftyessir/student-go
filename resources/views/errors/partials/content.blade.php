{{-- phase 26: isi halaman galat yang sama untuk semua kode (dipakai layouts.app dan errors.standalone).
     tanpa query, tanpa session, tanpa data pengguna: hanya request header + route(). jangan tampilkan detail teknis. --}}
@php
    // "kembali": referer dari host yang sama (bukan session, agar aman saat database mati), selain itu ke beranda
    $sgErrorBack = request()->headers->get('referer');
    if (! $sgErrorBack || parse_url($sgErrorBack, PHP_URL_HOST) !== request()->getHost() || $sgErrorBack === request()->fullUrl()) {
        $sgErrorBack = route('home');
    }
@endphp
<section class="sg-error" aria-labelledby="sg-error-title">
    <div class="sg-error__panel">
        <p class="sg-error__code"><span class="sr-only">Kode galat </span>{{ $code }}</p>
        <h1 id="sg-error-title" class="sg-error__title">{{ $title }}</h1>
        <p class="sg-error__desc">{{ $description }}</p>

        <div class="sg-error__actions">
            {{-- riwayat browser lebih dulu; tanpa javascript tautan tetap menuju referer atau beranda --}}
            <a href="{{ $sgErrorBack }}" class="sg-btn sg-btn--secondary" onclick="if (window.history.length > 1) { window.history.back(); return false; }">
                <x-icon name="arrow-left" size="sm" />
                Kembali
            </a>
            <a href="{{ route('home') }}" class="sg-btn sg-btn--primary">
                <x-icon name="house" size="sm" />
                Ke beranda
            </a>
        </div>

        <p class="sg-error__help">
            Butuh bantuan? <a href="{{ route('contact') }}">Hubungi tim Student Go</a>.
        </p>
    </div>
</section>
