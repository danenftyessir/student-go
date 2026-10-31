@extends('layouts.app')

@section('title', 'Edit Profil Instansi')

{{-- phase 20: form bersection dengan urutan dan label yang sama seperti registrasi instansi (data instansi,
     lokasi, penanggung jawab). kontrak tetap: PUT institution.profile.update, multipart, 12 field dengan
     name sama; id logo-input / province-select / regency-select tetap. nilai tersimpan selalu terpilih:
     jenis instansi teks bebas (mis. 'Dinas Kesehatan') tampil sebagai opsi terpilih tanpa mengubah data (D-06). --}}

@php
    // label sama dengan form registrasi; kunci = nilai yang divalidasi registrasi
    $standardTypes = [
        'pemerintah_desa' => 'Pemerintah desa',
        'dinas' => 'Dinas',
        'ngo' => 'Lembaga swadaya masyarakat (LSM/NGO)',
        'puskesmas' => 'Puskesmas',
        'sekolah' => 'Sekolah',
        'perguruan_tinggi' => 'Perguruan tinggi',
        'lainnya' => 'Lainnya',
    ];
    // nilai di luar daftar standar yang dikirim controller (nilai tersimpan, D-06)
    $storedTypes = array_diff_key($institutionTypes, $standardTypes);
    $currentType = (string) old('type', $institution->type);
    // old() yang tidak tersimpan dan tidak standar tetap terpilih setelah galat validasi
    if ($currentType !== '' && ! array_key_exists($currentType, $standardTypes) && ! array_key_exists($currentType, $storedTypes)) {
        $storedTypes[$currentType] = $currentType;
    }
@endphp

@section('content')
<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Edit profil instansi"
                      description="Perubahan nama, jenis, lokasi dan deskripsi langsung tampil di profil publik."
                      :back="route('institution.profile.index')" back-label="Kembali ke profil" />

    @if (session('success'))
        <x-ui.alert tone="success" class="sg-inst-page__errors">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert tone="danger" class="sg-inst-page__errors">{{ session('error') }}</x-ui.alert>
    @endif
    <x-ui.form-errors class="sg-inst-page__errors" />

    <form method="POST" action="{{ route('institution.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- 1. data instansi --}}
        <section class="sg-inst-section" aria-labelledby="judul-data">
            <h2 id="judul-data"><span class="sg-inst-section__num">1</span>Data instansi</h2>

            <x-ui.field label="Nama instansi" for="name" required>
                <x-ui.input name="name" :value="$institution->name" autocomplete="organization" maxlength="255" required />
            </x-ui.field>

            <div class="sg-inst-grid-2">
                <x-ui.field label="Jenis instansi" for="type" required>
                    <x-ui.select name="type" placeholder="Pilih jenis instansi" required>
                        @if ($storedTypes)
                            <optgroup label="Tersimpan saat ini">
                                @foreach ($storedTypes as $value => $label)
                                    <option value="{{ $value }}" @selected($currentType === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Pilihan standar">
                        @endif
                        @foreach ($standardTypes as $value => $label)
                            <option value="{{ $value }}" @selected($currentType === (string) $value)>{{ $label }}</option>
                        @endforeach
                        @if ($storedTypes)
                            </optgroup>
                        @endif
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Email resmi instansi" for="email" required help="Dipakai untuk masuk. Mengubah email meminta verifikasi email ulang.">
                    <x-ui.input name="email" type="email" :value="$user->email" autocomplete="email" maxlength="255" required help />
                </x-ui.field>
            </div>

            <x-ui.field label="Website (opsional)" for="website" help="Awali dengan https://">
                <x-ui.input name="website" type="url" :value="$institution->website" autocomplete="url" maxlength="255" placeholder="https://sukamaju.desa.id" help />
            </x-ui.field>

            <x-ui.field label="Deskripsi instansi (opsional)" for="description" help="Tampil di bagian 'Tentang instansi' pada profil publik.">
                <x-ui.textarea name="description" :value="$institution->description" rows="5" help
                               placeholder="Wilayah kerja, layanan utama, atau program yang sedang berjalan." />
            </x-ui.field>

            {{-- logo: pratinjau = logo tersimpan atau inisial (tanpa gambar pengganti) --}}
            <div class="sg-inst-logo">
                <x-ui.avatar id="logo-preview" :src="$institution->logo_path ? $institution->getLogoUrl() : null" :name="$institution->name"
                             size="xl" square alt="Logo {{ $institution->name }}" />
                <x-ui.upload name="logo" id="logo-input" label="Logo instansi (opsional)" accept="image/jpeg,image/jpg,image/png"
                             hint="JPG atau PNG, maks. 2 MB. Kosongkan bila logo tidak diganti." :max-size="2 * 1024 * 1024"
                             onchange="previewLogo(event)" />
            </div>
        </section>

        {{-- 2. lokasi: kabupaten/kota mengikuti provinsi (GET /api/regencies/{id}, sgRegions) --}}
        <section class="sg-inst-section" aria-labelledby="judul-lokasi">
            <h2 id="judul-lokasi"><span class="sg-inst-section__num">2</span>Lokasi</h2>

            <x-ui.field label="Alamat lengkap" for="address" required>
                <x-ui.textarea name="address" :value="$institution->address" rows="3" autocomplete="street-address" required />
            </x-ui.field>

            <div class="sg-inst-grid-2"
                 x-data="sgRegions({ url: @js(url('/api/regencies/__ID__')), province: @js((string) old('province_id', $institution->province_id)), regency: @js((string) old('regency_id', $institution->regency_id)), regencies: @js($regencies->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->values()) })">
                <x-ui.field label="Provinsi" for="province-select" name="province_id" required>
                    <x-ui.select name="province_id" id="province-select" :options="$provinces->pluck('name', 'id')->all()" :selected="$institution->province_id"
                                 placeholder="Pilih provinsi" required x-model="province" x-ref="province" @change="load()" />
                </x-ui.field>
                <x-ui.field label="Kabupaten/kota" for="regency-select" name="regency_id" required>
                    <x-ui.select name="regency_id" id="regency-select" placeholder="Pilih kabupaten/kota" required aria-describedby="regency-select-status"
                                 x-model="regency" x-ref="regency" x-bind:disabled="loading">
                        <template x-for="item in regencies" :key="item.id">
                            <option :value="item.id" x-text="item.name"></option>
                        </template>
                    </x-ui.select>
                    <p class="sg-inst-status" id="regency-select-status" aria-live="polite" :data-state="failed ? 'error' : null">
                        <span class="sg-spinner" aria-hidden="true" x-show="loading" x-cloak></span>
                        <span x-text="status || (province ? '' : 'Pilih provinsi terlebih dahulu.')"></span>
                        <button type="button" class="sg-btn sg-btn--tertiary" x-show="failed" x-cloak @click="load()">Coba lagi</button>
                    </p>
                </x-ui.field>
            </div>
        </section>

        {{-- 3. penanggung jawab --}}
        <section class="sg-inst-section" aria-labelledby="judul-pic">
            <h2 id="judul-pic"><span class="sg-inst-section__num">3</span>Penanggung jawab</h2>

            <div class="sg-inst-grid-2">
                <x-ui.field label="Nama penanggung jawab" for="pic_name" required>
                    <x-ui.input name="pic_name" :value="$institution->pic_name" autocomplete="name" maxlength="255" required />
                </x-ui.field>
                <x-ui.field label="Jabatan" for="pic_position" required>
                    <x-ui.input name="pic_position" :value="$institution->pic_position" autocomplete="organization-title" maxlength="255" required />
                </x-ui.field>
                <x-ui.field label="Nomor telepon" for="phone" required>
                    <x-ui.input name="phone" type="tel" :value="$institution->phone" autocomplete="tel" maxlength="20" required />
                </x-ui.field>
            </div>
        </section>

        <div class="sg-inst-savebar">
            <x-ui.button variant="tertiary" :href="route('institution.profile.index')">Batal</x-ui.button>
            <x-ui.button type="submit">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// pratinjau logo saat dipilih; bila pilihan dihapus, pratinjau kembali ke logo tersimpan
(function () {
    const preview = document.getElementById('logo-preview');
    const original = preview ? [...preview.childNodes].map((n) => n.cloneNode(true)) : [];
    let objectUrl = null;

    window.previewLogo = function (event) {
        if (!preview || event.target.name !== 'logo') return;
        const file = event.target.files && event.target.files[0];
        if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
        if (!file || !file.type.startsWith('image/')) {
            preview.replaceChildren(...original.map((n) => n.cloneNode(true)));
            return;
        }
        objectUrl = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = objectUrl;
        img.alt = 'Pratinjau logo baru';
        preview.replaceChildren(img);
    };
})();
</script>
@endpush
@endsection
