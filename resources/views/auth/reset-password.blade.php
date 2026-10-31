@extends('layouts.auth')

{{-- phase 6: template auth tunggal. form sama: POST password.update, field token (hidden), email, password,
     password_confirmation. syarat password = aturan ResetPasswordController (min 8, huruf besar/kecil, angka, simbol). --}}

@section('title', 'Reset Password')

@section('content')
    <x-ui.page-header title="Buat password baru" description="Password baru langsung berlaku untuk akun dengan email di bawah."
                      :back="route('login')" back-label="Kembali ke halaman masuk" />

    <form method="POST" action="{{ route('password.update') }}" class="sg-auth-form" novalidate x-data="sgAuthForm" @submit="submit($event)">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.field label="Email" for="email" required>
            <x-ui.input name="email" type="email" autocomplete="email" :value="$email" required />
            @include('auth.components.field-error', ['for' => 'email'])
        </x-ui.field>

        <x-ui.field label="Password baru" for="password" required>
            <x-ui.password name="password" rules autocomplete="new-password" required autofocus />
            @include('auth.components.field-error', ['for' => 'password'])
        </x-ui.field>

        <x-ui.field label="Konfirmasi password baru" for="password_confirmation" required>
            <x-ui.password name="password_confirmation" autocomplete="new-password" required data-sg-match="password" />
            @include('auth.components.field-error', ['for' => 'password_confirmation'])
        </x-ui.field>

        <x-ui.button type="submit" size="lg" block x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
            <span class="sg-spinner" aria-hidden="true" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? 'Menyimpan…' : 'Simpan password baru'">Simpan password baru</span>
        </x-ui.button>
    </form>
@endsection
