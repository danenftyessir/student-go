@extends('layouts.auth')

{{-- phase 6: template auth tunggal. form sama: POST password.email, field email. pesan server tidak diubah (keputusan user). --}}

@section('title', 'Lupa Password')

@section('content')
    <x-ui.page-header title="Lupa password" description="Masukkan email akun Anda. Kami kirimkan tautan untuk membuat password baru."
                      :back="route('login')" back-label="Kembali ke halaman masuk" />

    <form method="POST" action="{{ route('password.email') }}" class="sg-auth-form" novalidate x-data="sgAuthForm" @submit="submit($event)">
        @csrf

        <x-ui.field label="Email" for="email" required>
            <x-ui.input name="email" type="email" autocomplete="email" required autofocus />
            @include('auth.components.field-error', ['for' => 'email'])
        </x-ui.field>

        <x-ui.button type="submit" size="lg" block x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
            <span class="sg-spinner" aria-hidden="true" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? 'Mengirim…' : 'Kirim tautan reset'">Kirim tautan reset</span>
        </x-ui.button>

        <div class="sg-auth-note">
            Belum menerima email?
            <ul>
                <li>Periksa folder spam atau promosi.</li>
                <li>Tautan berlaku selama 60 menit.</li>
            </ul>
        </div>
    </form>
@endsection
