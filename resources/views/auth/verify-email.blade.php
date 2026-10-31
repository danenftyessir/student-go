@extends('layouts.auth')

{{-- phase 6: template auth tunggal. form sama: POST verification.resend (tanpa field) dan POST logout. --}}

@section('title', 'Verifikasi Email')

@section('content')
    <x-ui.page-header title="Verifikasi email Anda" description="Kami mengirim tautan verifikasi ke alamat berikut. Buka email itu dan pilih tautannya untuk mengaktifkan akun." />

    <div class="sg-auth-form">
        <x-ui.dl :items="['Email' => auth()->user()->email]" />

        @if (session('status') == 'verification-link-sent' && ! session('success'))
            <x-ui.alert tone="success">Tautan verifikasi baru telah dikirim ke email Anda.</x-ui.alert>
        @endif

        <div class="sg-auth-note">
            Tidak menemukan emailnya?
            <ul>
                <li>Periksa folder spam atau promosi.</li>
                <li>Tautan berlaku selama 60 menit.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('verification.resend') }}" x-data="sgAuthForm" @submit="submit($event)">
            @csrf
            <x-ui.button type="submit" size="lg" block x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
                <span class="sg-spinner" aria-hidden="true" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Mengirim…' : 'Kirim ulang email verifikasi'">Kirim ulang email verifikasi</span>
            </x-ui.button>
            @error('email')
                <p class="sg-error" role="alert"><x-icon name="circle-alert" size="sm" />{{ $message }}</p>
            @enderror
        </form>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="sg-auth-alt">
        @csrf
        Bukan akun Anda? <button type="submit" class="sg-auth-link">Keluar</button>
    </form>
@endsection
