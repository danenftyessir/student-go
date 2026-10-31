@extends('layouts.auth')

{{-- phase 6: registrasi instansi di template auth + stepper bersama. kontrak tetap: POST register.institution.submit
     (multipart, id institutionRegisterForm), 18 field dengan name/id/accept sama, kirim lewat fetch (json).
     provinsi -> kabupaten/kota tetap dari /api/public/regencies/{id}, kini dengan pembatalan request dan status (J30).
     sukses: panel status di halaman (pengganti alert()), tanpa login otomatis seperti sebelumnya. --}}

@section('title', 'Daftar sebagai Instansi')
@section('auth_width', 'wide')

@php
    // langkah hanya di sisi klien (endpoint validasi langkah instansi tidak dipakai halaman ini, sebelum maupun sesudah)
    $steps = [
        ['institution_name', 'institution_type', 'official_email', 'website', 'description', 'logo'],
        ['address', 'province_id', 'regency_id'],
        ['pic_name', 'pic_position', 'phone_number'],
        ['verification_document', 'ktp', 'npwp', 'username', 'password', 'password_confirmation'],
    ];
    $initialStep = 1;
    foreach ($steps as $i => $fields) {
        if ($errors->hasAny($fields)) { $initialStep = $i + 1; break; }
    }
    $types = [
        'pemerintah_desa' => 'Pemerintah desa',
        'dinas' => 'Dinas',
        'ngo' => 'Lembaga swadaya masyarakat (LSM/NGO)',
        'puskesmas' => 'Puskesmas',
        'sekolah' => 'Sekolah',
        'perguruan_tinggi' => 'Perguruan tinggi',
        'lainnya' => 'Lainnya',
    ];
@endphp

@section('aside')
    <h2 class="sg-auth-aside__title">Yang perlu disiapkan</h2>
    <ul class="sg-auth-aside__list">
        <li><span class="sg-auth-aside__icon"><x-icon name="mail" size="sm" /></span><span><strong>Email resmi instansi</strong>Dipakai untuk masuk dan menerima informasi verifikasi.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="map-pin" size="sm" /></span><span><strong>Alamat dan wilayah</strong>Alamat lengkap, provinsi, dan kabupaten/kota.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="user" size="sm" /></span><span><strong>Penanggung jawab</strong>Nama, jabatan, dan nomor telepon yang bisa dihubungi.</span></li>
        <li><span class="sg-auth-aside__icon"><x-icon name="file-text" size="sm" /></span><span><strong>Dokumen verifikasi</strong>Surat tugas, SK, akta, atau surat pengesahan (PDF, maks. 5 MB), foto KTP penanggung jawab dan NPWP instansi (JPG/PNG, maks. 2 MB).</span></li>
    </ul>
    <p class="sg-auth-aside__note">Akun instansi tidak langsung aktif setelah mendaftar. Dokumen ditinjau terlebih dahulu, dan informasi verifikasi dikirim ke email resmi instansi.</p>
@endsection

@section('content')
    <x-ui.page-header title="Daftar sebagai Instansi" description="Empat langkah. Siapkan dokumen verifikasi sebelum mulai."
                      :back="route('register')" back-label="Kembali ke pilihan peran" />

    <form method="POST" action="{{ route('register.institution.submit') }}" enctype="multipart/form-data" id="institutionRegisterForm"
          class="sg-wizard" novalidate
          x-data="sgRegister({ steps: @js($steps), initialStep: {{ $initialStep }}, submitUrl: @js(route('register.institution.submit')), mode: 'panel' })"
          @submit.prevent="step < total ? next() : submit()">
        @csrf

        @include('auth.components.wizard-done', ['title' => 'Pendaftaran instansi terkirim', 'next' => 'Dokumen Anda akan ditinjau. Anda dapat masuk setelah menerima informasi verifikasi di email resmi instansi.'])

        <div class="sg-wizard__content" x-show="!done">
        <x-ui.stepper :steps="['Data instansi', 'Lokasi', 'Penanggung jawab', 'Verifikasi dan akun']" :current="$initialStep" model="step" label="Kemajuan pendaftaran" class="sg-wizard__stepper" />

        @include('auth.components.wizard-feedback')

        {{-- langkah 1: data instansi --}}
        <section id="step1-content" data-step="1" class="sg-wizard__step" aria-labelledby="step1-title" x-show="step === 1" @if ($initialStep !== 1) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step1-title" class="sg-wizard__title" data-step-heading tabindex="-1">Data instansi</h2>
                <p class="sg-wizard__desc">Nama dan jenis instansi tampil di profil publik dan di setiap masalah yang Anda publikasikan.</p>
            </div>

            <x-ui.field label="Nama instansi" for="institution_name" required>
                <x-ui.input name="institution_name" autocomplete="organization" minlength="3" maxlength="255" required placeholder="Contoh: Pemerintah Desa Sukamaju" />
                @include('auth.components.field-error', ['for' => 'institution_name'])
            </x-ui.field>

            <x-ui.field label="Jenis instansi" for="institution_type" required>
                <x-ui.select name="institution_type" :options="$types" placeholder="Pilih jenis instansi" required />
                @include('auth.components.field-error', ['for' => 'institution_type'])
            </x-ui.field>

            <x-ui.field label="Email resmi instansi" for="official_email" required help="Dipakai untuk masuk dan menerima informasi verifikasi.">
                <x-ui.input name="official_email" type="email" autocomplete="email" maxlength="255" required help placeholder="Contoh: pemdes@sukamaju.desa.id" />
                @include('auth.components.field-error', ['for' => 'official_email'])
            </x-ui.field>

            <div class="sg-wizard__group">
                <h3 class="sg-wizard__group-title">Profil (opsional)</h3>
                <x-ui.field label="Website (opsional)" for="website">
                    <x-ui.input name="website" type="url" autocomplete="url" maxlength="255" placeholder="https://sukamaju.desa.id" />
                    @include('auth.components.field-error', ['for' => 'website'])
                </x-ui.field>
                <x-ui.field label="Deskripsi instansi (opsional)" for="description" help="Maksimal 1.000 karakter.">
                    <x-ui.textarea name="description" rows="4" maxlength="1000" help placeholder="Wilayah kerja, layanan utama, atau program yang sedang berjalan." />
                    @include('auth.components.field-error', ['for' => 'description'])
                </x-ui.field>
                <div>
                    <x-ui.upload name="logo" label="Logo instansi (opsional)" accept="image/*" hint="JPG atau PNG, maks. 2 MB" :max-size="2 * 1024 * 1024" />
                    @include('auth.components.field-error', ['for' => 'logo'])
                </div>
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 2: lokasi --}}
        <section id="step2-content" data-step="2" class="sg-wizard__step" aria-labelledby="step2-title" x-show="step === 2" @if ($initialStep !== 2) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step2-title" class="sg-wizard__title" data-step-heading tabindex="-1">Lokasi</h2>
                <p class="sg-wizard__desc">Mahasiswa menyaring masalah KKN menurut provinsi dan kabupaten/kota.</p>
            </div>

            <x-ui.field label="Alamat lengkap" for="address" required>
                <x-ui.textarea name="address" rows="3" maxlength="500" autocomplete="street-address" required placeholder="Contoh: Jl. Raya Sukamaju No. 12, RT 02/RW 05" />
                @include('auth.components.field-error', ['for' => 'address'])
            </x-ui.field>

            <div class="sg-auth-form__row sg-auth-form__row--2"
                 x-data="sgRegions({ url: @js(url('/api/public/regencies/__ID__')), province: @js((string) old('province_id')), regency: @js((string) old('regency_id')), regencies: @js($regencies) })">
                <x-ui.field label="Provinsi" for="province_id" required>
                    <x-ui.select name="province_id" :options="$provinces->pluck('name', 'id')->all()" placeholder="Pilih provinsi" required
                                 x-model="province" x-ref="province" @change="load()" />
                    @include('auth.components.field-error', ['for' => 'province_id'])
                </x-ui.field>
                <x-ui.field label="Kabupaten/kota" for="regency_id" required>
                    <x-ui.select name="regency_id" placeholder="Pilih kabupaten/kota" required aria-describedby="regency_id-status"
                                 x-model="regency" x-ref="regency" x-bind:disabled="loading">
                        <template x-for="item in regencies" :key="item.id">
                            <option :value="item.id" x-text="item.name"></option>
                        </template>
                    </x-ui.select>
                    <p class="sg-auth-status" id="regency_id-status" aria-live="polite" x-bind:data-state="failed ? 'error' : null">
                        <span class="sg-spinner" aria-hidden="true" x-show="loading" x-cloak></span>
                        <span x-text="status || (province ? '' : 'Pilih provinsi terlebih dahulu.')">Pilih provinsi terlebih dahulu.</span>
                        <button type="button" class="sg-auth-link" x-show="failed" x-cloak @click="load()">Coba lagi</button>
                    </p>
                    @include('auth.components.field-error', ['for' => 'regency_id'])
                </x-ui.field>
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 3: penanggung jawab --}}
        <section id="step3-content" data-step="3" class="sg-wizard__step" aria-labelledby="step3-title" x-show="step === 3" @if ($initialStep !== 3) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step3-title" class="sg-wizard__title" data-step-heading tabindex="-1">Penanggung jawab</h2>
                <p class="sg-wizard__desc">Orang yang dihubungi mahasiswa dan tim Student Go tentang kegiatan KKN.</p>
            </div>

            <div class="sg-auth-form__row sg-auth-form__row--2">
                <x-ui.field label="Nama penanggung jawab" for="pic_name" required>
                    <x-ui.input name="pic_name" autocomplete="name" maxlength="100" required placeholder="Contoh: Budi Santoso"
                                pattern="[A-Za-z\s.]+" data-sg-pattern="Nama penanggung jawab hanya boleh berisi huruf, spasi, dan titik." />
                    @include('auth.components.field-error', ['for' => 'pic_name'])
                </x-ui.field>
                <x-ui.field label="Jabatan" for="pic_position" required>
                    <x-ui.input name="pic_position" autocomplete="organization-title" maxlength="100" required placeholder="Contoh: Sekretaris Desa" />
                    @include('auth.components.field-error', ['for' => 'pic_position'])
                </x-ui.field>
            </div>

            <x-ui.field label="Nomor telepon" for="phone_number" required help="Diawali 0, 62, atau +62. Contoh: 081234567890.">
                <x-ui.input name="phone_number" type="tel" autocomplete="tel" inputmode="tel" required help />
                @include('auth.components.field-error', ['for' => 'phone_number'])
            </x-ui.field>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="button" icon-end="arrow-right" class="sg-wizard__primary" @click="next()">Lanjutkan</x-ui.button>
            </div>
        </section>

        {{-- langkah 4: verifikasi & akun --}}
        <section id="step4-content" data-step="4" class="sg-wizard__step" aria-labelledby="step4-title" x-show="step === 4" @if ($initialStep !== 4) x-cloak @endif>
            <div class="sg-wizard__head">
                <h2 id="step4-title" class="sg-wizard__title" data-step-heading tabindex="-1">Verifikasi dan akun</h2>
                <p class="sg-wizard__desc">Dokumen dipakai untuk memastikan instansi Anda benar ada. Setelah dikirim, dokumen ditinjau dan informasi verifikasi dikirim ke email resmi instansi.</p>
            </div>

            <div>
                <x-ui.upload name="verification_document" label="Dokumen verifikasi" accept="application/pdf" required
                             hint="Surat tugas, SK, akta, atau surat pengesahan. PDF, maks. 5 MB" :max-size="5 * 1024 * 1024" />
                @include('auth.components.field-error', ['for' => 'verification_document'])
            </div>
            <div class="sg-auth-form__row sg-auth-form__row--2">
                <div>
                    <x-ui.upload name="ktp" label="KTP penanggung jawab" accept="image/jpeg,image/jpg,image/png" required hint="JPG atau PNG, maks. 2 MB" :max-size="2 * 1024 * 1024" />
                    @include('auth.components.field-error', ['for' => 'ktp'])
                </div>
                <div>
                    <x-ui.upload name="npwp" label="NPWP instansi" accept="image/jpeg,image/jpg,image/png" required hint="JPG atau PNG, maks. 2 MB" :max-size="2 * 1024 * 1024" />
                    @include('auth.components.field-error', ['for' => 'npwp'])
                </div>
            </div>

            <div class="sg-wizard__group">
                <h3 class="sg-wizard__group-title">Akun</h3>
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
            </div>

            <div class="sg-wizard__actions">
                <x-ui.button type="button" variant="secondary" icon="arrow-left" @click="back()">Kembali</x-ui.button>
                <x-ui.button type="submit" class="sg-wizard__primary" x-bind:disabled="busy" x-bind:aria-busy="busy.toString()">
                    <span class="sg-spinner" aria-hidden="true" x-show="busy" x-cloak></span>
                    <span x-text="busy ? 'Mengirim…' : 'Kirim pendaftaran'">Kirim pendaftaran</span>
                </x-ui.button>
            </div>
        </section>
        </div>
    </form>

    <p class="sg-auth-alt">Sudah punya akun? <a href="{{ route('login') }}" class="sg-auth-link">Masuk</a></p>
@endsection
