@extends('layouts.app')

@section('title', 'Edit Profil Perusahaan')

{{-- phase 25: form bersection (pola edit profil instansi, phase 20) dengan pilihan dan label yang sama seperti registrasi
     perusahaan. kontrak tetap: PUT company.profile.update, multipart, name field sama (+ founded_year, city, province_id
     yang sudah divalidasi controller dan diisi saat registrasi). nilai tersimpan selalu terisi: email jatuh balik ke email
     akun, ukuran ke employee_count; nilai di luar daftar tampil terpilih di "Tersimpan saat ini" tanpa mengubah data (D-06). --}}

@php
    // daftar sama dengan auth/company-register (nilai yang dikirim = nilai registrasi)
    $industryList = ['Teknologi Informasi', 'Keuangan', 'E-commerce', 'Pendidikan', 'Kesehatan', 'Manufaktur', 'Retail', 'Konstruksi', 'Media & Hiburan', 'Transportasi', 'Lainnya'];
    $standardIndustries = array_combine($industryList, array_map(fn ($v) => $v === 'Retail' ? 'Ritel' : $v, $industryList));
    $standardSizes = ['1-10' => '1–10 karyawan', '11-50' => '11–50 karyawan', '51-200' => '51–200 karyawan', '201-500' => '201–500 karyawan', '501-1000' => '501–1.000 karyawan', '1000+' => 'Lebih dari 1.000 karyawan'];

    // nilai tersimpan (atau old() setelah galat) yang tidak ada di daftar standar
    $currentIndustry = (string) old('industry', $company->industry);
    $currentSize = (string) old('company_size', $company->company_size ?? $company->employee_count);
    $storedIndustry = $currentIndustry !== '' && ! array_key_exists($currentIndustry, $standardIndustries) ? $currentIndustry : null;
    $storedSize = $currentSize !== '' && ! array_key_exists($currentSize, $standardSizes) ? $currentSize : null;
@endphp

@section('content')
<div class="sg-container sg-co-profile-edit">
    <x-ui.page-header title="Edit profil perusahaan"
                      description="Nama, industri, deskripsi dan logo tampil di profil publik perusahaan."
                      :back="route('company.profile.index')" back-label="Kembali ke profil" />

    @if (session('error'))
        <x-ui.alert tone="danger" class="sg-co-profile-edit__alert">{{ session('error') }}</x-ui.alert>
    @endif
    <x-ui.form-errors class="sg-co-profile-edit__alert" />

    <form method="POST" action="{{ route('company.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- 1. informasi dasar --}}
        <section class="sg-co-section" aria-labelledby="judul-dasar">
            <h2 id="judul-dasar"><span class="sg-co-section__num">1</span>Informasi dasar</h2>

            <x-ui.field label="Nama perusahaan" for="name" required>
                <x-ui.input name="name" :value="$company->name" autocomplete="organization" maxlength="255" required />
            </x-ui.field>

            <x-ui.field label="Tagline (opsional)" for="tagline" help="Satu kalimat singkat di bawah nama perusahaan.">
                <x-ui.input name="tagline" :value="$company->tagline" maxlength="255" help placeholder="Contoh: Mitra pemasaran hasil tani Garut" />
            </x-ui.field>

            <div class="sg-co-grid-2">
                <x-ui.field label="Industri" for="industry">
                    <x-ui.select name="industry" placeholder="Pilih industri">
                        @if ($storedIndustry)
                            <optgroup label="Tersimpan saat ini">
                                <option value="{{ $storedIndustry }}" selected>{{ $storedIndustry }}</option>
                            </optgroup>
                            <optgroup label="Pilihan standar">
                        @endif
                        @foreach ($standardIndustries as $value => $label)
                            <option value="{{ $value }}" @selected($currentIndustry === (string) $value)>{{ $label }}</option>
                        @endforeach
                        @if ($storedIndustry)
                            </optgroup>
                        @endif
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Jumlah karyawan" for="company_size">
                    <x-ui.select name="company_size" placeholder="Pilih jumlah karyawan">
                        @if ($storedSize)
                            <optgroup label="Tersimpan saat ini">
                                <option value="{{ $storedSize }}" selected>{{ $storedSize }} karyawan</option>
                            </optgroup>
                            <optgroup label="Pilihan standar">
                        @endif
                        @foreach ($standardSizes as $value => $label)
                            <option value="{{ $value }}" @selected($currentSize === (string) $value)>{{ $label }}</option>
                        @endforeach
                        @if ($storedSize)
                            </optgroup>
                        @endif
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Tahun berdiri (opsional)" for="founded_year">
                    <x-ui.input name="founded_year" type="number" :value="$company->founded_year" inputmode="numeric" min="1800" max="{{ date('Y') }}" placeholder="Contoh: 2015" />
                </x-ui.field>
            </div>

            <x-ui.field label="Deskripsi perusahaan (opsional)" for="description" help="Tampil di bagian 'Tentang perusahaan' pada profil.">
                <x-ui.textarea name="description" :value="$company->description" rows="5" help
                               placeholder="Bidang usaha, produk, atau program magang yang tersedia." />
            </x-ui.field>

            {{-- logo: pratinjau = logo tersimpan atau inisial (tanpa gambar pengganti) --}}
            <div class="sg-co-logo">
                <x-ui.avatar id="logo-preview" :src="$company->logo ? $company->logo_url : null" :name="$company->name"
                             size="xl" square alt="Logo {{ $company->name }}" />
                <div class="sg-co-logo__controls">
                    <x-ui.upload name="logo" id="logo-input" label="Logo perusahaan (opsional)" accept="image/jpeg,image/jpg,image/png,image/webp"
                                 hint="JPG, PNG, atau WEBP, maks. 5 MB. Kosongkan bila logo tidak diganti." :max-size="5 * 1024 * 1024"
                                 onchange="previewLogo(event)" />
                    @if ($company->logo)
                        <x-ui.button variant="danger" icon="trash-2" onclick="deleteCompanyLogo()">Hapus logo</x-ui.button>
                    @endif
                </div>
            </div>
        </section>

        {{-- 2. kontak dan alamat --}}
        <section class="sg-co-section" aria-labelledby="judul-kontak">
            <h2 id="judul-kontak"><span class="sg-co-section__num">2</span>Kontak dan alamat</h2>

            <div class="sg-co-grid-2">
                <x-ui.field label="Email perusahaan" for="email" help="Email kontak di profil. Email untuk masuk tidak berubah.">
                    <x-ui.input name="email" type="email" :value="$company->email ?? $company->user?->email" autocomplete="email" maxlength="255" help />
                </x-ui.field>
                <x-ui.field label="Nomor telepon (opsional)" for="phone" help="Diawali 0, 62, atau +62.">
                    <x-ui.input name="phone" type="tel" :value="$company->phone" autocomplete="tel" inputmode="tel" maxlength="20" help placeholder="Contoh: 0221234567" />
                </x-ui.field>
            </div>

            <x-ui.field label="Website (opsional)" for="website" help="Awali dengan https://">
                <x-ui.input name="website" type="url" :value="$company->website" autocomplete="url" maxlength="255" help placeholder="https://perusahaan.co.id" />
            </x-ui.field>

            <x-ui.field label="Alamat lengkap (opsional)" for="address">
                <x-ui.textarea name="address" :value="$company->address" rows="3" maxlength="500" autocomplete="street-address"
                               placeholder="Contoh: Jl. Merdeka No. 12, Kelurahan Sukajadi" />
            </x-ui.field>

            <div class="sg-co-grid-2">
                <x-ui.field label="Kota/kabupaten (opsional)" for="city">
                    <x-ui.input name="city" :value="$company->city" autocomplete="address-level2" maxlength="100" placeholder="Contoh: Kabupaten Garut" />
                </x-ui.field>
                <x-ui.field label="Provinsi (opsional)" for="province_id">
                    <x-ui.select name="province_id" :options="$provinces->pluck('name', 'id')->all()" :selected="$company->province_id" placeholder="Pilih provinsi" />
                </x-ui.field>
                <x-ui.field label="Lokasi (opsional)" for="location" help="Teks lokasi singkat, mis. kota dan negara.">
                    <x-ui.input name="location" :value="$company->location" maxlength="255" help placeholder="Contoh: Bandung, Indonesia" />
                </x-ui.field>
                <x-ui.field label="Kode pos (opsional)" for="postal_code">
                    <x-ui.input name="postal_code" :value="$company->postal_code" autocomplete="postal-code" inputmode="numeric" maxlength="20" placeholder="Contoh: 40115" />
                </x-ui.field>
            </div>
        </section>

        {{-- 3. media sosial --}}
        <section class="sg-co-section" aria-labelledby="judul-sosial">
            <h2 id="judul-sosial"><span class="sg-co-section__num">3</span>Media sosial <span class="sg-co-section__opt">opsional</span></h2>

            <div class="sg-co-grid-2">
                <x-ui.field label="LinkedIn" for="linkedin">
                    <x-ui.input name="linkedin" type="url" :value="$company->linkedin" maxlength="255" placeholder="https://linkedin.com/company/…" />
                </x-ui.field>
                <x-ui.field label="Twitter/X" for="twitter">
                    <x-ui.input name="twitter" type="url" :value="$company->twitter" maxlength="255" placeholder="https://x.com/…" />
                </x-ui.field>
                <x-ui.field label="Facebook" for="facebook">
                    <x-ui.input name="facebook" type="url" :value="$company->facebook" maxlength="255" placeholder="https://facebook.com/…" />
                </x-ui.field>
                <x-ui.field label="Instagram" for="instagram">
                    <x-ui.input name="instagram" type="url" :value="$company->instagram" maxlength="255" placeholder="https://instagram.com/…" />
                </x-ui.field>
            </div>
        </section>

        <div class="sg-co-savebar">
            <x-ui.button variant="tertiary" :href="route('company.profile.index')">Batal</x-ui.button>
            <x-ui.button type="submit">Simpan perubahan</x-ui.button>
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

// hapus logo tersimpan (DELETE company.profile.delete-logo, sama seperti sebelumnya)
async function deleteCompanyLogo() {
    const yes = await window.sgConfirm({ title: 'Hapus logo?', message: 'Logo dihapus dari profil; inisial nama perusahaan tampil sebagai gantinya.', confirmLabel: 'Hapus' });
    if (!yes) return;
    try {
        const response = await fetch(@js(route('company.profile.delete-logo')), {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'Accept': 'application/json' },
        });
        const data = await response.json().catch(() => ({}));
        if (data.success) {
            window.location.reload();
        } else {
            window.showNotification?.('Logo gagal dihapus.', 'error');
        }
    } catch (error) {
        console.error('Gagal menghapus logo:', error);
        window.showNotification?.('Logo gagal dihapus.', 'error');
    }
}
</script>
@endpush
@endsection
