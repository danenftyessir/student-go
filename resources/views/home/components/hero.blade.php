{{-- pembuka beranda (AD-11): video latar berulang tanpa suara di belakang judul. poster adalah gambar pertama (LCP);
     video dipilih dan dimuat oleh resources/js/student-go/hero-video.js. tanpa js, gerak dikurangi, atau hemat data: poster saja.
     berkas: public/video/hero-loop-{desktop,mobile}.{webm,mp4,webp,jpg} (loop asli proyek, lihat student-go-docs/design-system/VIDEO_SOURCES.md). --}}
<section class="sg-hero" aria-labelledby="home-title" data-sg-hero
         data-video-desktop-webm="{{ asset('video/hero-loop-desktop.webm') }}"
         data-video-desktop-mp4="{{ asset('video/hero-loop-desktop.mp4') }}"
         data-video-mobile-webm="{{ asset('video/hero-loop-mobile.webm') }}"
         data-video-mobile-mp4="{{ asset('video/hero-loop-mobile.mp4') }}">
    <div class="sg-hero__media" aria-hidden="true">
        <video class="sg-hero__video" muted loop playsinline preload="none" aria-hidden="true" tabindex="-1" disablepictureinpicture disableremoteplayback></video>
        <picture>
            <source media="(max-width: 767px)" type="image/webp" srcset="{{ asset('video/hero-loop-mobile.webp') }}" width="720" height="1280">
            <source media="(max-width: 767px)" type="image/jpeg" srcset="{{ asset('video/hero-loop-mobile.jpg') }}" width="720" height="1280">
            <source type="image/webp" srcset="{{ asset('video/hero-loop-desktop.webp') }}" width="1920" height="1080">
            <img class="sg-hero__poster" src="{{ asset('video/hero-loop-desktop.jpg') }}" alt="" width="1920" height="1080" fetchpriority="high">
        </picture>
    </div>
    <div class="sg-hero__scrim" aria-hidden="true"></div>

    <div class="sg-container sg-hero__inner">
        <div class="sg-hero__content">
            <h1 id="home-title" class="sg-hero__title">Platform digital untuk Kuliah Kerja Nyata</h1>
            <p class="sg-hero__lead">Student Go mempertemukan instansi dengan mahasiswa KKN.</p>

            <div class="sg-hero__actions">
                @guest
                    <x-ui.button :href="route('register')" size="lg" icon-end="arrow-right" class="sg-hero__btn sg-hero__btn--primary" data-hero-primary>Daftar</x-ui.button>
                    <x-ui.button :href="route('login')" variant="secondary" size="lg" class="sg-hero__btn sg-hero__btn--secondary">Masuk</x-ui.button>
                @else
                    @if ($dashboardRoute && Route::has($dashboardRoute))
                        <x-ui.button :href="route($dashboardRoute)" size="lg" icon-end="arrow-right" class="sg-hero__btn sg-hero__btn--primary" data-hero-primary>Buka dashboard</x-ui.button>
                    @endif
                @endguest
            </div>
        </div>
    </div>

    {{-- penanda 1px di dasar pembuka: header berubah padat setelah penanda ini lewat di bawah header --}}
    <span class="sg-hero__sentinel" data-sg-hero-sentinel aria-hidden="true"></span>
</section>
