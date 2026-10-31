{{-- resources/views/components/footer.blade.php (phase 3)
     permukaan gelap .sg-on-dark: teks #f4f6fb / #c9d1ec di atas #171c2e (kontras >= 4.5:1).
     tautan 'Kebijakan Privasi' (sebelumnya ke '#') disembunyikan sampai halamannya ada (D-23). --}}
<footer class="sg-on-dark sg-footer">
    <div class="sg-container">
        <div class="sg-footer__top">
            <div>
                <a href="{{ route('home') }}" class="sg-footer__brand inline-block" aria-label="Student Go, ke beranda">
                    <img src="{{ asset('assets/brand/student-go-logo-on-dark.svg') }}" alt="" width="132" height="28">
                </a>
                <p class="sg-footer__tagline">
                    Platform digital yang menghubungkan mahasiswa dengan instansi untuk program Kuliah Kerja Nyata berkelanjutan.
                </p>
            </div>

            <nav class="sg-footer__nav" aria-label="Tautan kaki halaman">
                <h2>Jelajahi</h2>
                <ul>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                    <li><a href="{{ route('contact') }}">Kontak</a></li>
                </ul>
            </nav>
        </div>

        <p class="sg-footer__legal">
            Hak cipta © {{ date('Y') }}. Seluruh hak cipta dilindungi undang-undang dan terdaftar pada Direktorat Jendral Kekayaan Intelektual Republik Indonesia.
        </p>
    </div>
</footer>
