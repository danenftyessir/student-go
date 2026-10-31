@extends('layouts.auth')

{{-- phase 6: template auth tunggal. form sama: POST route('login'), field email (email atau username) + password;
     "remember" ditambahkan (sudah dibaca LoginController). tanpa statistik dan tanpa foto (J9, D-19). --}}

@section('title', 'Masuk')

@section('aside')
    @include('auth.components.aside-roles')
@endsection

@section('content')
    <x-ui.page-header title="Masuk ke Student Go" description="Gunakan email atau username yang Anda pakai saat mendaftar." />

    <form method="POST" action="{{ route('login') }}" class="sg-auth-form" novalidate x-data="sgAuthForm" @submit="submit($event)">
        @csrf

        <x-ui.field label="Email atau username" for="email" required>
            <x-ui.input name="email" type="text" autocomplete="username" autocapitalize="none" spellcheck="false"
                        required autofocus data-sg-label="Email atau username" />
            @include('auth.components.field-error', ['for' => 'email'])
        </x-ui.field>

        <x-ui.field label="Password" for="password" required>
            <x-ui.password name="password" autocomplete="current-password" required data-sg-label="Password" />
            @include('auth.components.field-error', ['for' => 'password'])
        </x-ui.field>

        <div class="sg-auth-form__inline">
            <x-ui.checkbox name="remember" id="remember" value="1" label="Ingat saya di perangkat ini" />
            <a href="{{ route('password.request') }}" class="sg-auth-link">Lupa password?</a>
        </div>

        <x-ui.button type="submit" size="lg" block x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
            <span class="sg-spinner" aria-hidden="true" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? 'Memproses…' : 'Masuk'">Masuk</span>
        </x-ui.button>
    </form>

    <p class="sg-auth-alt">Belum punya akun? <a href="{{ route('register') }}" class="sg-auth-link">Daftar</a></p>
@endsection
