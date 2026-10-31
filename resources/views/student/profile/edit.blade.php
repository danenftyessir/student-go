@extends('layouts.app')

@section('title', 'Edit Profil')

{{-- phase 12: form bersection (Identitas, Akademik, Kontak, Portofolio, Keamanan) dengan navigasi bagian: kolom kiri
     yang menempel mulai 1024px, daftar tautan di atas form pada layar kecil. kontrak tetap: PUT student.profile.update
     (multipart, name/id sama) dan form password terpisah PUT student.profile.password.update.
     nilai tersimpan selalu terisi: stories/skills/interests sudah di-cast array oleh model (bukan json). --}}
@php
    $sections = [
        'identitas' => ['label' => 'Identitas', 'fields' => ['profile_photo', 'first_name', 'last_name']],
        'akademik' => ['label' => 'Akademik', 'fields' => ['university_id', 'major', 'nim', 'semester']],
        'kontak' => ['label' => 'Kontak', 'fields' => ['email', 'whatsapp_number']],
        'portofolio' => ['label' => 'Portofolio', 'fields' => ['bio', 'stories', 'skills', 'interests']],
        'keamanan' => ['label' => 'Keamanan', 'fields' => ['current_password', 'password', 'password_confirmation']],
    ];
    // jumlah galat server per bagian ('stories.0' -> 'stories')
    $sectionErrors = [];
    foreach ($sections as $key => $section) {
        $sectionErrors[$key] = collect($errors->keys())->filter(fn ($k) => in_array(explode('.', $k)[0], $section['fields'], true))->count();
    }
    $listValue = fn (string $field) => (array) old($field, $student->{$field} ?? []);
    $nameError = fn (string $field) => $errors->first($field);
@endphp

@section('content')
<div class="sg-container pb-16">
    <x-ui.page-header title="Edit profil"
                      description="Perbarui identitas, data akademik, kontak dan isi portofolio Anda."
                      :back="route('student.profile.index')" back-label="Kembali ke profil" />

    @if (session('error'))
        <x-ui.alert tone="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif
    <x-ui.form-errors class="mb-4" />

    <div class="grid gap-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:gap-12" x-data="sgSectionNav(@js(array_keys($sections)))">
        {{-- navigasi bagian --}}
        <nav class="min-w-0 border-b border-gray-200 pb-4 lg:sticky lg:top-20 lg:self-start lg:border-b-0 lg:pb-0" aria-label="Bagian formulir">
            <ol class="m-0 grid grid-cols-2 gap-1 p-0 sm:grid-cols-3 lg:grid-cols-1 lg:gap-0.5 lg:border-l lg:border-gray-200">
                @foreach ($sections as $key => $section)
                    <li class="list-none">
                        <a href="#bagian-{{ $key }}" @click.prevent="go('{{ $key }}')"
                           :aria-current="active === '{{ $key }}' ? 'true' : null"
                           class="flex min-h-10 items-center gap-2 rounded-sm px-3 py-2 text-sm text-gray-700 no-underline transition-colors hover:bg-gray-50 hover:text-gray-900 aria-[current=true]:bg-brand-50 aria-[current=true]:font-semibold aria-[current=true]:text-gray-900 lg:-ml-px lg:rounded-none lg:border-l-2 lg:border-transparent lg:aria-[current=true]:border-brand-600 lg:aria-[current=true]:bg-transparent">
                            <span class="flex-1">{{ $section['label'] }}</span>
                            @if ($sectionErrors[$key])
                                <x-icon name="circle-alert" size="sm" class="text-danger-700" />
                                <span class="sr-only">, ada isian yang perlu diperbaiki</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="min-w-0 max-w-3xl">
            {{-- ========== form 1: profil ========== --}}
            {{-- baris berulang yang kosong tidak dikirim: aturan stories.*/skills.* (string) menolak null, controller membuang yang kosong --}}
            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data"
                  @submit="$el.querySelectorAll('[data-row] input, [data-row] textarea').forEach((f) => { if (f.value.trim() === '') f.disabled = true; })">
                @csrf
                @method('PUT')

                {{-- 1. identitas --}}
                <section id="bagian-identitas" data-section="identitas" class="scroll-mt-24 space-y-5 border-b border-gray-200 pb-8" aria-labelledby="judul-identitas">
                    <h2 id="judul-identitas" tabindex="-1" class="m-0 text-lg leading-7 font-semibold text-gray-900">Identitas</h2>

                    {{-- foto: pratinjau = foto tersimpan atau inisial nama kanonik (tanpa gambar pengganti) --}}
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <x-ui.avatar id="photo-preview" :src="$student->profile_photo_path ? $student->profile_photo_url : null"
                                     :name="$student->display_name" size="xl" alt="Foto profil {{ $student->display_name }}" />
                        <x-ui.upload name="profile_photo" id="profile_photo" label="Foto profil (opsional)" accept="image/jpeg,image/jpg,image/png"
                                     hint="JPG, JPEG atau PNG, maks. 2 MB. Kosongkan bila foto tidak diganti." :max-size="2 * 1024 * 1024"
                                     class="min-w-0 flex-1" onchange="previewProfilePhoto(event)" />
                    </div>

                    {{-- name lalu value berurutan (dibaca uji phase 1); kelas dan aria sama seperti x-ui.input --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Nama depan" for="first_name" required>
                            <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" id="first_name"
                                   class="sg-input" maxlength="50" autocomplete="given-name" required
                                   @if ($nameError('first_name')) aria-invalid="true" aria-describedby="first_name-error" data-touched @endif>
                        </x-ui.field>
                        <x-ui.field label="Nama belakang" for="last_name" required>
                            <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" id="last_name"
                                   class="sg-input" maxlength="50" autocomplete="family-name" required
                                   @if ($nameError('last_name')) aria-invalid="true" aria-describedby="last_name-error" data-touched @endif>
                        </x-ui.field>
                    </div>
                    <p class="m-0 text-sm text-gray-600">Nama ini tampil di seluruh Student Go, termasuk profil publik.</p>
                </section>

                {{-- 2. akademik --}}
                <section id="bagian-akademik" data-section="akademik" class="scroll-mt-24 space-y-5 border-b border-gray-200 py-8" aria-labelledby="judul-akademik">
                    <h2 id="judul-akademik" tabindex="-1" class="m-0 text-lg leading-7 font-semibold text-gray-900">Akademik</h2>

                    <x-ui.field label="Universitas" for="university_id" required>
                        <x-ui.select name="university_id" :options="$universities->pluck('name', 'id')->all()" :selected="$student->university_id"
                                     placeholder="Pilih universitas" required />
                    </x-ui.field>

                    <x-ui.field label="Jurusan" for="major" required>
                        <x-ui.input name="major" :value="$student->major" maxlength="100" required placeholder="Contoh: Teknik Lingkungan" />
                    </x-ui.field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="NIM" for="nim" required>
                            <x-ui.input name="nim" :value="$student->nim" maxlength="20" inputmode="numeric" required />
                        </x-ui.field>
                        <x-ui.field label="Semester" for="semester" required>
                            <x-ui.select name="semester" :options="collect(range(1, 14))->mapWithKeys(fn ($i) => [$i => 'Semester ' . $i])->all()"
                                         :selected="$student->semester" placeholder="Pilih semester" required />
                        </x-ui.field>
                    </div>
                </section>

                {{-- 3. kontak --}}
                <section id="bagian-kontak" data-section="kontak" class="scroll-mt-24 space-y-5 border-b border-gray-200 py-8" aria-labelledby="judul-kontak">
                    <h2 id="judul-kontak" tabindex="-1" class="m-0 text-lg leading-7 font-semibold text-gray-900">Kontak</h2>
                    <p class="m-0 flex gap-1.5 text-sm text-gray-600"><x-icon name="lock" size="sm" class="mt-0.5 flex-none" />Email dan WhatsApp hanya terlihat oleh Anda dan koneksi yang sudah diterima.</p>

                    <x-ui.field label="Email" for="email" required help="Email ini juga dipakai untuk masuk ke Student Go.">
                        <x-ui.input name="email" type="email" :value="$user->email" maxlength="255" autocomplete="email" help required />
                    </x-ui.field>

                    <x-ui.field label="Nomor WhatsApp" for="whatsapp_number" required help="Diawali 08, 62 atau +62. Contoh: 081234567890">
                        <x-ui.input name="whatsapp_number" type="tel" :value="$student->whatsapp_number" inputmode="tel" autocomplete="tel" help required />
                    </x-ui.field>
                </section>

                {{-- 4. portofolio --}}
                <section id="bagian-portofolio" data-section="portofolio" class="scroll-mt-24 space-y-6 py-8" aria-labelledby="judul-portofolio">
                    <h2 id="judul-portofolio" tabindex="-1" class="m-0 text-lg leading-7 font-semibold text-gray-900">Portofolio</h2>

                    <x-ui.field label="Bio (opsional)" for="bio" help="Satu atau dua kalimat di bawah nama Anda. Maksimal 500 karakter.">
                        <x-ui.textarea name="bio" :value="$student->bio" rows="3" maxlength="500" help
                                       placeholder="Contoh: Mahasiswa teknik lingkungan yang tertarik pada pengelolaan air bersih desa." />
                    </x-ui.field>

                    <fieldset class="min-w-0 border-0 p-0">
                        <legend class="sg-label">Cerita pengalaman (opsional)</legend>
                        <p class="sg-help mb-3">Apa yang Anda kerjakan selama KKN, bersama siapa, dan apa yang dipelajari. Maksimal 2.000 karakter per cerita.</p>
                        @include('student.profile.components.repeat-list', [
                            'field' => 'stories', 'label' => 'Cerita', 'items' => $listValue('stories'), 'multiline' => true, 'maxlength' => 2000,
                            'placeholder' => 'Contoh: Saat mengerjakan proyek desa wisata, saya belajar mendengarkan aspirasi warga…', 'addLabel' => 'Tambah cerita',
                        ])
                    </fieldset>

                    <fieldset class="min-w-0 border-0 p-0">
                        <legend class="sg-label">Keterampilan (opsional)</legend>
                        <p class="sg-help mb-3">Satu keterampilan per baris, maksimal 50 karakter.</p>
                        @include('student.profile.components.repeat-list', [
                            'field' => 'skills', 'label' => 'Keterampilan', 'items' => $listValue('skills'), 'multiline' => false, 'maxlength' => 50,
                            'placeholder' => 'Contoh: Pemetaan GIS', 'addLabel' => 'Tambah keterampilan',
                        ])
                    </fieldset>

                    <fieldset class="min-w-0 border-0 p-0">
                        <legend class="sg-label">Minat (opsional)</legend>
                        <p class="sg-help mb-3">Satu minat per baris, maksimal 50 karakter.</p>
                        @include('student.profile.components.repeat-list', [
                            'field' => 'interests', 'label' => 'Minat', 'items' => $listValue('interests'), 'multiline' => false, 'maxlength' => 50,
                            'placeholder' => 'Contoh: Kesehatan masyarakat', 'addLabel' => 'Tambah minat',
                        ])
                    </fieldset>
                </section>

                <div class="sticky bottom-0 z-10 flex flex-wrap justify-end gap-2 border-t border-gray-200 bg-gray-50 py-3 pb-[calc(.75rem+env(safe-area-inset-bottom,0px))]">
                    <x-ui.button variant="secondary" :href="route('student.profile.index')">Batal</x-ui.button>
                    <x-ui.button type="submit">Simpan perubahan</x-ui.button>
                </div>
            </form>

            {{-- ========== form 2: password (terpisah) ========== --}}
            <form action="{{ route('student.profile.password.update') }}" method="POST" class="mt-10">
                @csrf
                @method('PUT')

                <section id="bagian-keamanan" data-section="keamanan" class="scroll-mt-24 space-y-5 border-t border-gray-200 pt-8" aria-labelledby="judul-keamanan">
                    <h2 id="judul-keamanan" tabindex="-1" class="m-0 text-lg leading-7 font-semibold text-gray-900">Keamanan</h2>
                    <p class="m-0 text-sm text-gray-600">Ubah password di sini. Form ini disimpan terpisah; setelah password diganti, Anda perlu masuk lagi.</p>

                    <x-ui.field label="Password lama" for="current_password" required>
                        <x-ui.password name="current_password" autocomplete="current-password" required />
                    </x-ui.field>
                    <x-ui.field label="Password baru" for="password" required help="Minimal 8 karakter dan berbeda dari password lama.">
                        <x-ui.password name="password" autocomplete="new-password" aria-describedby="password-help" required />
                    </x-ui.field>
                    <x-ui.field label="Konfirmasi password baru" for="password_confirmation" required>
                        <x-ui.password name="password_confirmation" autocomplete="new-password" required />
                    </x-ui.field>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="secondary" icon="lock">Ubah password</x-ui.button>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // navigasi bagian: tandai bagian yang sedang terlihat; klik memindahkan fokus ke judul bagian
    Alpine.data('sgSectionNav', (keys) => ({
        active: keys[0],
        init() {
            const sections = keys.map((key) => document.getElementById('bagian-' + key)).filter(Boolean);
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) this.active = entry.target.dataset.section;
                });
            }, { rootMargin: '-30% 0px -60% 0px' });
            sections.forEach((section) => observer.observe(section));
            const hash = location.hash.replace('#bagian-', '');
            if (keys.includes(hash)) this.$nextTick(() => this.go(hash, false));
        },
        go(key, push = true) {
            const section = document.getElementById('bagian-' + key);
            if (!section) return;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            section.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
            section.querySelector('h2').focus({ preventScroll: true });
            this.active = key;
            if (push) history.replaceState(null, '', '#bagian-' + key);
        },
    }));

    // baris isian berulang (cerita, keterampilan, minat)
    Alpine.data('sgRepeatList', (label) => ({
        add() {
            const row = this.$refs.row.content.firstElementChild.cloneNode(true);
            this.$refs.list.appendChild(row);
            this.renumber();
            row.querySelector('input, textarea').focus();
        },
        remove(button) {
            const row = button.closest('[data-row]');
            const next = row.nextElementSibling || row.previousElementSibling;
            row.remove();
            this.renumber();
            (next ? next.querySelector('input, textarea') : this.$refs.add).focus();
        },
        renumber() {
            this.$refs.list.querySelectorAll('[data-row]').forEach((row, index) => {
                row.querySelector('input, textarea').setAttribute('aria-label', label + ' ' + (index + 1));
                row.querySelector('[data-remove]').setAttribute('aria-label', 'Hapus ' + label.toLowerCase() + ' ' + (index + 1));
            });
        },
    }));
});

// pratinjau foto profil yang dipilih (unggah tetap lewat input profile_photo)
function previewProfilePhoto(event) {
    const input = event.target;
    if (!input || input.type !== 'file') return;
    const file = input.files && input.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = () => {
        const box = document.getElementById('photo-preview');
        let img = box.querySelector('img');
        if (!img) {
            box.textContent = '';
            box.removeAttribute('role');
            box.removeAttribute('aria-label');
            img = document.createElement('img');
            img.alt = 'Pratinjau foto profil';
            box.appendChild(img);
        }
        img.src = reader.result;
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
