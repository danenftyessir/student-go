{{-- panel sukses registrasi instansi/perusahaan (pengganti alert()). pesan dari respons server; tombol ke masuk dan ke redirect_url. --}}
<div class="sg-auth-done" x-show="done" x-cloak x-ref="done" tabindex="-1" role="status">
    <h2 class="sg-auth-done__title"><x-icon name="circle-check" />{{ $title }}</h2>
    <p x-text="done && done.message"></p>
    <p>{{ $next }}</p>
    <div class="sg-auth-done__actions">
        <x-ui.button href="{{ route('login') }}" icon="log-in">Masuk</x-ui.button>
        <a class="sg-btn sg-btn--secondary" x-bind:href="(done && done.redirect) || @js(route('home'))" href="{{ route('home') }}">Ke beranda</a>
    </div>
</div>
