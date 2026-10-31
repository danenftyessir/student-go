@extends('layouts.auth')

{{-- phase 6: registrasi mahasiswa di template auth + stepper bersama. kontrak tetap: POST register.student.submit
     (multipart, id studentRegisterForm), 13 field dengan name/id sama, validasi per langkah ke validation.student.step,
     kirim akhir lewat fetch (json) lalu ke redirect_url (dashboard mahasiswa). --}}

@section('title', 'Daftar sebagai Mahasiswa')
@section('auth_width', 'wide')

@php
    // field per langkah = pembagian di ValidationController::validateStudentStep (+ terms di langkah 3)
    $steps = [
        ['first_name', 'last_name', 'email', 'whatsapp_number', 'profile_photo'],
        ['university_id', 'major', 'nim', 'semester'],
        ['username', 'password', 'password_confirmation', 'terms'],
    ];
    $initialStep = 1;
    foreach ($steps as $i => $fields) {
        if ($errors->hasAny($fields)) { $initialStep = $i + 1; break; }
    }
@endphp

@section('aside')
    <h2 class="sg-auth-aside__title">Yang perlu disiapkan</h2>
    <ul class="sg-auth-aside__list">
        <li><span class="sg-auth-aside__icon"><x-icon name="mail" size="sm" /></span><span><strong>Email dan nomor WhatsApp</strong>Email aktif untuk masuk dan nomor WhatsApp minimal 10 digit.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="graduation-cap" size="sm" /></span><span><strong>Data akademik</strong>Universitas, jurusan, NIM, dan semester yang sedang berjalan.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="image" size="sm" /></span><span><strong>Foto profil (opsional)</strong>JPG atau PNG, maksimal 2 MB. Bisa ditambahkan nanti di profil.</span></li>
    </ul>
    <p class="sg-auth-aside__note">Setelah mendaftar, Anda langsung masuk ke dashboard mahasiswa.</p>
@endsection

@section('content')
    <x-ui.page-header title="Daftar sebagai Mahasiswa" description="Tiga langkah singkat. Data dapat diubah lagi di halaman profil."
                      :back="route('register')" back-label="Kembali ke pilihan peran" />

    <form method="POST" action="{{ route('register.student.submit') }}" enctype="multipart/form-data" id="studentRegisterForm"
          class="sg-wizard" novalidate
          x-data="sgRegister({ steps: @js($steps), initialStep: {{ $initialStep }}, validateUrl: @js(route('validation.student.step')), submitUrl: @js(route('register.student.submit')), mode: 'redirect' })"
          @submit.prevent="step < total ? next() : submit()">
        @csrf

        <x-ui.stepper :steps="['Data pribadi', 'Data akademik', 'Buat akun']" :current="$initialStep" model="step" label="Kemajuan pendaftaran" class="sg-wizard__stepper" />

        @include('auth.components.wizard-feedback')

        {{-- langkah 1: data pribadi --}}
        <section id="step1-content" data-step="1" class="sg-wizard__step" aria-labelledby="step1-title" x-show="step === 1" @if ($initialStep !== 1) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step1-title" class="sg-wizard__title" data-step-heading tabindex="-1">Data pribadi</h2>
                <p class="sg-wizard__desc">Nama Anda tampil di profil dan lamaran.</p>
            </div>

            <div class="sg-auth-form__row sg-auth-form__row--2">
                <x-ui.field label="Nama depan" for="first_name" required>
                    <x-ui.input name="first_name" autocomplete="given-name" maxlength="50" required placeholder="Contoh: Ahmad" />
                    @include('auth.components.field-error', ['for' => 'first_name'])
                </x-ui.field>
                <x-ui.field label="Nama belakang" for="last_name" required>
                    <x-ui.input name="last_name" autocomplete="family-name" maxlength="50" required placeholder="Contoh: Hidayat" />
                    @include('auth.components.field-error', ['for' => 'last_name'])
                </x-ui.field>
            </div>

            <x-ui.field label="Email" for="email" required help="Email kampus atau email pribadi yang aktif. Dipakai untuk masuk.">
                <x-ui.input name="email" type="email" autocomplete="email" maxlength="255" required help placeholder="nama@kampus.ac.id" />
                @include('auth.components.field-error', ['for' => 'email'])
            </x-ui.field>

            <x-ui.field label="Nomor WhatsApp" for="whatsapp_number" required help="Minimal 10 digit, misalnya 081234567890.">
                <x-ui.input name="whatsapp_number" type="tel" autocomplete="tel" inputmode="tel" minlength="10" maxlength="20" required help />
                @include('auth.components.field-error', ['for' => 'whatsapp_number'])
            </x-ui.field>

            <div>
                <x-ui.upload name="profile_photo" label="Foto profil (opsional)" accept="image/*" hint="JPG atau PNG, maks. 2 MB" :max-size="2 * 1024 * 1024" />
                @include('auth.components.field-error', ['for' => 'profile_photo'])
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()" x-bind:disabled="busy" x-bind:aria-busy="busy.toString()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 2: data akademik --}}
        <section id="step2-content" data-step="2" class="sg-wizard__step" aria-labelledby="step2-title" x-show="step === 2" @if ($initialStep !== 2) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step2-title" class="sg-wizard__title" data-step-heading tabindex="-1">Data akademik</h2>
                <p class="sg-wizard__desc">Instansi melihat universitas dan jurusan Anda saat meninjau lamaran.</p>
            </div>

            <x-ui.field label="Universitas" for="university_id" required>
                <x-ui.select name="university_id" :options="$universities->pluck('name', 'id')->all()" placeholder="Pilih universitas" autocomplete="organization" required />
                @include('auth.components.field-error', ['for' => 'university_id'])
            </x-ui.field>

            <x-ui.field label="Jurusan" for="major" required>
                <x-ui.input name="major" autocomplete="off" maxlength="100" required placeholder="Contoh: Teknik Lingkungan" />
                @include('auth.components.field-error', ['for' => 'major'])
            </x-ui.field>

            <div class="sg-auth-form__row sg-auth-form__row--2">
                <x-ui.field label="NIM" for="nim" required>
                    <x-ui.input name="nim" autocomplete="off" maxlength="20" required placeholder="Contoh: 15321045" />
                    @include('auth.components.field-error', ['for' => 'nim'])
                </x-ui.field>
                <x-ui.field label="Semester" for="semester" required>
                    <x-ui.select name="semester" :options="collect(range(1, 14))->mapWithKeys(fn ($i) => [$i => 'Semester '.$i])->all()" placeholder="Pilih semester" required />
                    @include('auth.components.field-error', ['for' => 'semester'])
                </x-ui.field>
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()" x-bind:disabled="busy" x-bind:aria-busy="busy.toString()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 3: akun --}}
        <section id="step3-content" data-step="3" class="sg-wizard__step" aria-labelledby="step3-title" x-show="step === 3" @if ($initialStep !== 3) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step3-title" class="sg-wizard__title" data-step-heading tabindex="-1">Buat akun</h2>
                <p class="sg-wizard__desc">Anda dapat masuk dengan username atau email.</p>
            </div>

            <x-ui.field label="Username" for="username" required help="Minimal 4 karakter.">
                <x-ui.input name="username" autocomplete="username" autocapitalize="none" spellcheck="false" minlength="4" maxlength="50" required help />
                @include('auth.components.field-error', ['for' => 'username'])
            </x-ui.field>

            <x-ui.field label="Password" for="password" required>
                <x-ui.password name="password" rules autocomplete="new-password" required />
                @include('auth.components.field-error', ['for' => 'password'])
            </x-ui.field>

            <x-ui.field label="Konfirmasi password" for="password_confirmation" required>
                <x-ui.password name="password_confirmation" autocomplete="new-password" required data-sg-match="password" />
                @include('auth.components.field-error', ['for' => 'password_confirmation'])
            </x-ui.field>

            <div>
                {{-- RN-16 / D-38: nama pihak Student Go, tanpa tautan ke halaman yang belum ada --}}
                <x-ui.checkbox name="terms" id="terms" value="on" label="Saya menyetujui syarat dan ketentuan serta kebijakan privasi Student Go."
                               required data-sg-required="Centang persetujuan untuk menyelesaikan pendaftaran." />
                @include('auth.components.field-error', ['for' => 'terms'])
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="submit" class="sg-wizard__primary" x-bind:disabled="busy" x-bind:aria-busy="busy.toString()">
                    <span class="sg-spinner" aria-hidden="true" x-show="busy" x-cloak></span>
                    <span x-text="busy ? 'Mendaftarkan…' : 'Daftar'">Daftar</span>
                </x-ui.button>
            </div>
        </section>
    </form>

    <p class="sg-auth-alt">Sudah punya akun? <a href="{{ route('login') }}" class="sg-auth-link">Masuk</a></p>
@endsection
