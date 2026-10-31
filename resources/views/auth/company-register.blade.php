@extends('layouts.auth')

{{-- phase 6: registrasi perusahaan, dari satu form panjang menjadi wizard bersama (4 langkah).
     kontrak tetap: POST register.company.submit (multipart), 17 field dengan name/id/accept sama, kirim lewat fetch (json).
     sukses: panel status di halaman (pengganti alert() + pindah ke beranda); tidak ada login otomatis seperti sebelumnya. --}}

@section('title', 'Daftar sebagai Perusahaan')
@section('auth_width', 'wide')

@php
    $steps = [
        ['company_name', 'industry', 'company_size', 'founded_year', 'website', 'description', 'logo'],
        ['address', 'city', 'province_id'],
        ['email', 'phone', 'pic_name', 'pic_position'],
        ['username', 'password', 'password_confirmation'],
    ];
    $initialStep = 1;
    foreach ($steps as $i => $fields) {
        if ($errors->hasAny($fields)) { $initialStep = $i + 1; break; }
    }
    // nilai yang dikirim tidak berubah; hanya label
    $industries = ['Teknologi Informasi', 'Keuangan', 'E-commerce', 'Pendidikan', 'Kesehatan', 'Manufaktur', 'Retail', 'Konstruksi', 'Media & Hiburan', 'Transportasi', 'Lainnya'];
    $industryOptions = array_combine($industries, array_map(fn ($v) => $v === 'Retail' ? 'Ritel' : $v, $industries));
    $sizes = ['1-10' => '1–10 karyawan', '11-50' => '11–50 karyawan', '51-200' => '51–200 karyawan', '201-500' => '201–500 karyawan', '501-1000' => '501–1.000 karyawan', '1000+' => 'Lebih dari 1.000 karyawan'];
@endphp

@section('aside')
    <h2 class="sg-auth-aside__title">Yang perlu disiapkan</h2>
    <ul class="sg-auth-aside__list">
        <li><span class="sg-auth-aside__icon"><x-icon name="briefcase" size="sm" /></span><span><strong>Data perusahaan</strong>Nama, industri, dan jumlah karyawan. Tahun berdiri, website, dan deskripsi boleh menyusul.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="mail" size="sm" /></span><span><strong>Email perusahaan</strong>Dipakai untuk masuk ke akun.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="user" size="sm" /></span><span><strong>Penanggung jawab</strong>Nama dan jabatan orang yang mengelola rekrutmen.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="image" size="sm" /></span><span><strong>Logo (opsional)</strong>PNG, JPG, atau WEBP, maksimal 2 MB.</span></li>
    </ul>
    <p class="sg-auth-aside__note">Setelah mendaftar, masuk dengan username atau email perusahaan.</p>
@endsection

@section('content')
    <x-ui.page-header title="Daftar sebagai Perusahaan" description="Empat langkah. Data perusahaan dapat dilengkapi lagi di halaman profil."
                      :back="route('register')" back-label="Kembali ke pilihan peran" />

    <form method="POST" action="{{ route('register.company.submit') }}" enctype="multipart/form-data"
          class="sg-wizard" novalidate
          x-data="sgRegister({ steps: @js($steps), initialStep: {{ $initialStep }}, submitUrl: @js(route('register.company.submit')), mode: 'panel' })"
          @submit.prevent="step < total ? next() : submit()">
        @csrf

        @include('auth.components.wizard-done', ['title' => 'Akun perusahaan dibuat', 'next' => 'Masuk dengan username atau email perusahaan untuk mulai mencari talenta.'])

        <div class="sg-wizard__content" x-show="!done">
        <x-ui.stepper :steps="['Data perusahaan', 'Lokasi', 'Kontak', 'Akun']" :current="$initialStep" model="step" label="Kemajuan pendaftaran" class="sg-wizard__stepper" />

        @include('auth.components.wizard-feedback')

        {{-- langkah 1: data perusahaan --}}
        <section id="step1-content" data-step="1" class="sg-wizard__step" aria-labelledby="step1-title" x-show="step === 1" @if ($initialStep !== 1) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step1-title" class="sg-wizard__title" data-step-heading tabindex="-1">Data perusahaan</h2>
                <p class="sg-wizard__desc">Mahasiswa melihat data ini di setiap lowongan yang Anda terbitkan.</p>
            </div>

            <x-ui.field label="Nama perusahaan" for="company_name" required>
                <x-ui.input name="company_name" autocomplete="organization" minlength="3" maxlength="255" required placeholder="Contoh: CV Tani Makmur Sejahtera" />
                @include('auth.components.field-error', ['for' => 'company_name'])
            </x-ui.field>

            <div class="sg-auth-form__row sg-auth-form__row--2">
                <x-ui.field label="Industri" for="industry" required>
                    <x-ui.select name="industry" :options="$industryOptions" placeholder="Pilih industri" required />
                    @include('auth.components.field-error', ['for' => 'industry'])
                </x-ui.field>
                <x-ui.field label="Jumlah karyawan" for="company_size" required>
                    <x-ui.select name="company_size" :options="$sizes" placeholder="Pilih jumlah karyawan" required />
                    @include('auth.components.field-error', ['for' => 'company_size'])
                </x-ui.field>
            </div>

            <div class="sg-wizard__group">
                <h3 class="sg-wizard__group-title">Profil (opsional)</h3>
                <div class="sg-auth-form__row sg-auth-form__row--2">
                    <x-ui.field label="Tahun berdiri (opsional)" for="founded_year">
                        <x-ui.input name="founded_year" type="number" inputmode="numeric" min="1800" max="{{ date('Y') }}" placeholder="Contoh: 2015" />
                        @include('auth.components.field-error', ['for' => 'founded_year'])
                    </x-ui.field>
                    <x-ui.field label="Website (opsional)" for="website">
                        <x-ui.input name="website" type="url" autocomplete="url" maxlength="255" placeholder="https://perusahaan.co.id" />
                        @include('auth.components.field-error', ['for' => 'website'])
                    </x-ui.field>
                </div>
                <x-ui.field label="Deskripsi perusahaan (opsional)" for="description" help="Maksimal 1.000 karakter.">
                    <x-ui.textarea name="description" rows="4" maxlength="1000" help placeholder="Bidang usaha, produk, atau program magang yang tersedia." />
                    @include('auth.components.field-error', ['for' => 'description'])
                </x-ui.field>
                <div>
                    <x-ui.upload name="logo" label="Logo perusahaan (opsional)" accept="image/png,image/jpeg,image/jpg,image/webp" hint="PNG, JPG, atau WEBP, maks. 2 MB" :max-size="2 * 1024 * 1024" />
                    @include('auth.components.field-error', ['for' => 'logo'])
                </div>
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 2: lokasi (semua opsional) --}}
        <section id="step2-content" data-step="2" class="sg-wizard__step" aria-labelledby="step2-title" x-show="step === 2" @if ($initialStep !== 2) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step2-title" class="sg-wizard__title" data-step-heading tabindex="-1">Lokasi</h2>
                <p class="sg-wizard__desc">Opsional. Boleh dilewati dan dilengkapi nanti di profil perusahaan.</p>
            </div>

            <x-ui.field label="Alamat lengkap (opsional)" for="address">
                <x-ui.textarea name="address" rows="3" maxlength="500" autocomplete="street-address" placeholder="Contoh: Jl. Merdeka No. 12, Kelurahan Sukajadi" />
                @include('auth.components.field-error', ['for' => 'address'])
            </x-ui.field>

            <div class="sg-auth-form__row sg-auth-form__row--2">
                <x-ui.field label="Kota/kabupaten (opsional)" for="city">
                    <x-ui.input name="city" autocomplete="address-level2" maxlength="100" placeholder="Contoh: Kabupaten Garut" />
                    @include('auth.components.field-error', ['for' => 'city'])
                </x-ui.field>
                <x-ui.field label="Provinsi (opsional)" for="province_id">
                    <x-ui.select name="province_id" :options="$provinces->pluck('name', 'id')->all()" placeholder="Pilih provinsi" />
                    @include('auth.components.field-error', ['for' => 'province_id'])
                </x-ui.field>
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 3: kontak --}}
        <section id="step3-content" data-step="3" class="sg-wizard__step" aria-labelledby="step3-title" x-show="step === 3" @if ($initialStep !== 3) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step3-title" class="sg-wizard__title" data-step-heading tabindex="-1">Kontak</h2>
                <p class="sg-wizard__desc">Email perusahaan dipakai untuk masuk.</p>
            </div>

            <div class="sg-auth-form__row sg-auth-form__row--2">
                <x-ui.field label="Email perusahaan" for="email" required>
                    <x-ui.input name="email" type="email" autocomplete="email" maxlength="255" required placeholder="info@perusahaan.co.id" />
                    @include('auth.components.field-error', ['for' => 'email'])
                </x-ui.field>
                <x-ui.field label="Nomor telepon (opsional)" for="phone" help="Diawali 0, 62, atau +62.">
                    <x-ui.input name="phone" type="tel" autocomplete="tel" inputmode="tel" help placeholder="Contoh: 0221234567" />
                    @include('auth.components.field-error', ['for' => 'phone'])
                </x-ui.field>
            </div>

            <div class="sg-auth-form__row sg-auth-form__row--2">
                <x-ui.field label="Nama penanggung jawab" for="pic_name" required>
                    <x-ui.input name="pic_name" autocomplete="name" maxlength="100" required placeholder="Contoh: Siti Rahmawati"
                                pattern="[A-Za-z\s.]+" data-sg-pattern="Nama penanggung jawab hanya boleh berisi huruf, spasi, dan titik." />
                    @include('auth.components.field-error', ['for' => 'pic_name'])
                </x-ui.field>
                <x-ui.field label="Jabatan" for="pic_position" required>
                    <x-ui.input name="pic_position" autocomplete="organization-title" maxlength="100" required placeholder="Contoh: Manajer SDM" />
                    @include('auth.components.field-error', ['for' => 'pic_position'])
                </x-ui.field>
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 4: akun --}}
        <section id="step4-content" data-step="4" class="sg-wizard__step" aria-labelledby="step4-title" x-show="step === 4" @if ($initialStep !== 4) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step4-title" class="sg-wizard__title" data-step-heading tabindex="-1">Akun</h2>
                <p class="sg-wizard__desc">Anda dapat masuk dengan username atau email perusahaan.</p>
            </div>

            <x-ui.field label="Username" for="username" required help="Minimal 4 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.">
                <x-ui.input name="username" autocomplete="username" autocapitalize="none" spellcheck="false" minlength="4" maxlength="50" required help
                            pattern="[A-Za-z0-9._\-]+" data-sg-pattern="Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung." />
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

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="submit" class="sg-wizard__primary" x-bind:disabled="busy" x-bind:aria-busy="busy.toString()">
                    <span class="sg-spinner" aria-hidden="true" x-show="busy" x-cloak></span>
                    <span x-text="busy ? 'Mendaftarkan…' : 'Daftar'">Daftar</span>
                </x-ui.button>
            </div>
        </section>
        </div>
    </form>

    <p class="sg-auth-alt">Sudah punya akun? <a href="{{ route('login') }}" class="sg-auth-link">Masuk</a></p>
@endsection
