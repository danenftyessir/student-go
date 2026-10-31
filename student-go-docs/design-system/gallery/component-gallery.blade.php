{{-- galeri komponen phase 3 (khusus pengembangan, TANPA route). dirender ke component-gallery.html dengan:
     php artisan tinker --execute="\Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8000'); file_put_contents(base_path('student-go-docs/design-system/gallery/component-gallery.html'), str_replace('https://127.0.0.1:8000', 'http://127.0.0.1:8000', view()->file(base_path('student-go-docs/design-system/gallery/component-gallery.blade.php'))->render()));"
     (AppServiceProvider memaksa skema https; di CLI hasilnya https://127.0.0.1:8000, maka diganti http untuk server lokal)
     buka lewat origin aplikasi (sprite ikon harus same-origin), mis. playwright page.route('/__sg-gallery'). data di sini adalah contoh uji panjang teks, bukan data produk. --}}
@extends('layouts.app')

@section('title', 'Galeri Komponen')

@php
    $inst = 'Dinas Sosial Pemberdayaan Perempuan Dan Perlindungan Anak Kabupaten Sidoarjo'; // 76 karakter
    $doc = 'Kajian Pengelolaan Air Bersih Berbasis Masyarakat di Desa Pesisir';                // 65 karakter
    $email = 'fatimah.azzahra.nurhidayati@students.itb-ganesha.id';                            // 51 karakter
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 20), 77, 20, 2, ['path' => url('/__sg-gallery'), 'query' => ['search' => 'air']]);
    $first = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 20), 77, 20, 1, ['path' => url('/__sg-gallery')]);
    $errors = (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag([
        'email_error' => ['Format email tidak valid.'],
        'dokumen_error' => ['Berkas harus berformat PDF.'],
        'peran_error' => ['Pilih salah satu peran.'],
    ]));
    view()->share('errors', $errors);
    $statuses = [];
    foreach (\App\Support\StatusVocabulary::domains() as $d) {
        $statuses[$d] = array_keys(\App\Support\StatusVocabulary::values($d));
    }
@endphp

@section('content')
<div class="sg-container pb-16">
    <x-ui.page-header title="Galeri Komponen" description="Setiap komponen bersama Student Go dalam setiap keadaannya (Phase 3)."
        :breadcrumbs="['Dokumentasi' => url('/__sg-gallery'), 'Galeri Komponen' => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="download">Sekunder</x-ui.button>
            <x-ui.button icon="plus">Aksi utama</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-10">
        <section id="g-buttons" aria-labelledby="h-buttons" class="grid gap-3">
            <h2 id="h-buttons" class="text-xl">Tombol</h2>
            <div class="flex flex-wrap gap-2">
                <x-ui.button>Primer</x-ui.button>
                <x-ui.button variant="secondary">Sekunder</x-ui.button>
                <x-ui.button variant="tertiary">Tersier</x-ui.button>
                <x-ui.button variant="danger" icon="trash-2">Hapus</x-ui.button>
                <x-ui.button variant="danger-solid">Hapus permanen</x-ui.button>
                <x-ui.button size="lg" icon-end="arrow-right">Besar</x-ui.button>
                <x-ui.button disabled>Nonaktif</x-ui.button>
                <x-ui.button loading>Menyimpan…</x-ui.button>
                <x-ui.button variant="secondary" href="#g-forms">Tautan</x-ui.button>
                <x-ui.icon-button icon="pencil" label="Ubah" bordered />
                <x-ui.icon-button icon="trash-2" label="Hapus" />
            </div>
        </section>

        <section id="g-forms" aria-labelledby="h-forms" class="grid gap-4 max-w-2xl">
            <h2 id="h-forms" class="text-xl">Formulir</h2>
            <x-ui.form-errors />
            <x-ui.field label="Nama instansi" for="nama" required help="Nama resmi sesuai dokumen.">
                <x-ui.input name="nama" :value="$inst" required help />
            </x-ui.field>
            <x-ui.field label="Email" for="email_error" required>
                <x-ui.input name="email_error" type="email" :value="$email" required />
            </x-ui.field>
            <x-ui.field label="Nonaktif" for="nonaktif">
                <x-ui.input name="nonaktif" value="Tidak dapat diubah" disabled />
            </x-ui.field>
            <x-ui.field label="Deskripsi" for="deskripsi">
                <x-ui.textarea name="deskripsi" value="Paragraf panjang untuk menguji pembungkusan teks pada textarea." />
            </x-ui.field>
            <x-ui.field label="Status" for="status">
                <x-ui.select name="status" placeholder="Semua status" :options="['pending' => 'Menunggu', 'reviewed' => 'Ditinjau', 'accepted' => 'Diterima', 'rejected' => 'Ditolak']" selected="reviewed" />
            </x-ui.field>
            <x-ui.search name="q" label="Cari dokumen" placeholder="Cari judul, instansi, atau wilayah" value="" />
            <x-ui.fieldset legend="Peran" name="peran_error" required>
                <x-ui.radio name="peran_error" value="student" label="Mahasiswa" />
                <x-ui.radio name="peran_error" value="institution" label="Instansi" />
                <x-ui.radio name="peran_error" value="company" label="Perusahaan" disabled />
            </x-ui.fieldset>
            <x-ui.fieldset legend="Notifikasi">
                <x-ui.checkbox name="notif[]" value="email" label="Kirim ringkasan lewat email" checked />
                <x-ui.checkbox name="notif[]" value="wa" :label="'Teks panjang: '.$inst" />
            </x-ui.fieldset>
            <x-ui.upload name="proposal" label="Proposal" accept=".pdf" hint="PDF, maks. 5 MB" :max-size="5242880" required
                :current="['name' => 'proposal-lama.pdf', 'url' => null]" />
            <x-ui.upload name="dokumen_error" label="Dokumen pendukung (galat server)" accept=".pdf,.docx" multiple hint="PDF atau DOCX, boleh lebih dari satu" />
        </section>

        <section id="g-feedback" aria-labelledby="h-feedback" class="grid gap-3 max-w-2xl">
            <h2 id="h-feedback" class="text-xl">Umpan balik</h2>
            <x-ui.alert>Informasi: data wilayah memakai 5 provinsi contoh.</x-ui.alert>
            <x-ui.alert tone="success" title="Tersimpan">Profil berhasil diperbarui.</x-ui.alert>
            <x-ui.alert tone="warning">Batas pendaftaran tinggal 3 hari.</x-ui.alert>
            <x-ui.alert tone="danger" title="Gagal mengunggah">Ukuran berkas melebihi 5 MB.</x-ui.alert>
            <div class="flex flex-wrap gap-2">
                <x-ui.button variant="secondary" id="toast-success" onclick="showNotification('Lamaran berhasil dikirim.', 'success')">Toast sukses</x-ui.button>
                <x-ui.button variant="secondary" id="toast-error" onclick="showNotification('Gagal menyimpan. Coba lagi.', 'error')">Toast galat</x-ui.button>
                <x-ui.button variant="secondary" data-sg-open="g-modal" id="open-modal">Buka modal</x-ui.button>
                <x-ui.button variant="secondary" data-sg-open="g-drawer" id="open-drawer">Buka drawer</x-ui.button>
            </div>
            <form id="g-confirm-form" method="GET" action="{{ url('/__sg-gallery-submit') }}" data-sg-confirm="Dokumen ini akan dihapus dari repositori." data-sg-confirm-title="Hapus dokumen?" data-sg-confirm-label="Hapus">
                <input type="hidden" name="dokumen" value="160">
                <x-ui.button type="submit" variant="danger" icon="trash-2" name="aksi" value="hapus">Hapus dengan konfirmasi</x-ui.button>
            </form>
            <x-ui.loading />
            <x-ui.loading :lines="3" label="Memuat daftar" />
        </section>

        <x-ui.modal id="g-modal" title="Ubah catatan">
            <x-ui.field label="Catatan" for="catatan"><x-ui.textarea name="catatan" /></x-ui.field>
            <x-slot:footer>
                <x-ui.button variant="secondary" data-sg-close>Batal</x-ui.button>
                <x-ui.button data-sg-close>Simpan</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
        <x-ui.modal id="g-drawer" title="Filter" drawer>
            <p>Isi drawer samping.</p>
        </x-ui.modal>

        <section id="g-data" aria-labelledby="h-data" class="grid gap-4">
            <h2 id="h-data" class="text-xl">Data</h2>
            <x-ui.summary :items="[['value' => 77, 'label' => 'masalah'], ['value' => 1250, 'label' => 'dokumen'], ['value' => null, 'label' => 'tidak tampil']]" />
            <div class="flex flex-wrap gap-2">
                @foreach ($statuses['application'] as $v)<x-ui.status domain="application" :value="$v" />@endforeach
                <x-ui.status domain="project" value="cancelled" />
            </div>
            <div class="flex flex-wrap gap-2" id="g-status-all">
                @foreach ($statuses as $domain => $values)
                    @foreach ($values as $v)<x-ui.status :domain="$domain" :value="$v" />@endforeach
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.chip icon="target">SDG 6 · Air Bersih dan Sanitasi Layak</x-ui.chip>
                <x-ui.chip>Pertanian</x-ui.chip>
                <x-ui.role-badge role="student" /><x-ui.role-badge role="institution" /><x-ui.role-badge role="company" />
            </div>

            <x-ui.tabs label="Filter status" :items="[
                ['label' => 'Semua', 'href' => url('/__sg-gallery'), 'active' => true, 'count' => 77],
                ['label' => 'Menunggu', 'href' => url('/__sg-gallery?status=pending'), 'count' => 12],
                ['label' => 'Diterima', 'href' => url('/__sg-gallery?status=accepted'), 'count' => 3],
            ]" />

            <x-ui.table caption="Dokumen repositori (contoh)">
                <x-slot:head>
                    <th scope="col">Judul</th><th scope="col">Instansi</th><th scope="col">Kontak</th><th scope="col">Status</th><th scope="col" class="sg-num">Unduhan</th>
                </x-slot:head>
                @foreach ([[$doc, $inst, $email, 'approved', 1250], ['Profil Desa Wisata', 'Pemkab Bantul', 'desa@bantul.go.id', 'pending', 8]] as [$t, $i, $e, $s, $n])
                    <tr>
                        <td data-label="Judul"><a href="#">{{ $t }}</a><span class="sg-table__meta">PDF · 1,4 MB</span></td>
                        <td data-label="Instansi">{{ $i }}</td>
                        <td data-label="Kontak">{{ $e }}</td>
                        <td data-label="Status"><x-ui.status domain="document" :value="$s" /></td>
                        <td data-label="Unduhan" class="sg-num">{{ number_format($n, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </x-ui.table>

            <x-ui.list label="Anggota jaringan (contoh)">
                <x-ui.list-item title="Farah Wibowo" href="#" :meta="$email">
                    <x-slot:leading><x-ui.avatar name="Farah Wibowo" decorative /></x-slot:leading>
                    <x-slot:end><x-ui.status domain="friend" value="accepted" /></x-slot:end>
                </x-ui.list-item>
                <x-ui.list-item :title="$inst" meta="Instansi · Jawa Timur">
                    <x-slot:leading><x-ui.avatar :name="$inst" square decorative /></x-slot:leading>
                    <x-slot:end><x-ui.button variant="secondary">Lihat</x-ui.button></x-slot:end>
                </x-ui.list-item>
            </x-ui.list>

            <div id="g-pagination" class="grid gap-3">
                <x-ui.pagination :paginator="$paginator" />
                <x-ui.pagination :paginator="$first" />
            </div>

            <x-ui.filter-bar :action="url('/__sg-gallery-submit')" search="search" search-placeholder="Cari masalah" :reset-href="url('/__sg-gallery')" :active="1" id="g-filter">
                <x-ui.field label="Provinsi" for="province_id"><x-ui.select name="province_id" placeholder="Semua provinsi" :options="[31 => 'DKI Jakarta', 32 => 'Jawa Barat', 35 => 'Jawa Timur']" /></x-ui.field>
                <x-ui.field label="Status" for="f-status"><x-ui.select name="status" id="f-status" placeholder="Semua status" :options="['open' => 'Dibuka', 'closed' => 'Ditutup']" selected="open" /></x-ui.field>
            </x-ui.filter-bar>

            <div class="grid gap-6 sm:grid-cols-2">
                <x-ui.dl :items="['Instansi' => $inst, 'Lokasi' => 'Kabupaten Sidoarjo, Jawa Timur', 'Kuota' => '4 mahasiswa', 'Kosong' => null]" />
                <x-ui.dl inline :items="['Tenggat' => '12 Desember 2026', 'Durasi' => '6 minggu']" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" id="g-cards">
                <x-ui.card :title="$doc" href="#" image="{{ asset('assets/illustrations/workflows/project-status.svg') }}" image-alt="Diagram status proyek">
                    <p class="text-sm text-gray-600">{{ $inst }}</p>
                    <x-slot:footer><x-ui.status domain="problem" value="open" /></x-slot:footer>
                </x-ui.card>
                <x-ui.card title="Gambar gagal dimuat (area media disembunyikan)" image="{{ url('/assets/tidak-ada.jpg') }}">
                    <p class="text-sm text-gray-600">Tanpa placeholder.</p>
                </x-ui.card>
            </div>
        </section>

        <section id="g-complex" aria-labelledby="h-complex" class="grid gap-4">
            <h2 id="h-complex" class="text-xl">Komponen kompleks</h2>
            <x-ui.stepper :steps="['Akun', 'Data diri', 'Akademik', 'Selesai']" :current="2" />
            <div x-data="{ step: 1 }" class="grid gap-2" id="g-stepper-live">
                <x-ui.stepper :steps="['Akun', 'Data instansi', 'Verifikasi']" model="step" />
                <div class="flex gap-2">
                    <x-ui.button variant="secondary" @click="step = Math.max(1, step - 1)" id="step-prev">Sebelumnya</x-ui.button>
                    <x-ui.button @click="step = Math.min(3, step + 1)" id="step-next">Berikutnya</x-ui.button>
                </div>
            </div>

            <x-ui.tablist :tabs="['ringkasan' => 'Ringkasan', 'dokumen' => 'Dokumen', 'ulasan' => 'Ulasan']" prefix="gt" label="Detail proyek">
                <x-ui.tab-panel id="ringkasan" prefix="gt" active>Ringkasan proyek.</x-ui.tab-panel>
                <x-ui.tab-panel id="dokumen" prefix="gt">Daftar dokumen.</x-ui.tab-panel>
                <x-ui.tab-panel id="ulasan" prefix="gt">Ulasan instansi.</x-ui.tab-panel>
            </x-ui.tablist>

            <div class="flex flex-wrap items-center gap-3" id="g-avatars">
                <x-ui.avatar name="Farah Wibowo" size="xs" />
                <x-ui.avatar name="Farah Wibowo" size="sm" />
                <x-ui.avatar name="Farah Wibowo" />
                <x-ui.avatar name="Farah Wibowo" size="lg" src="{{ url('/assets/foto-tidak-ada.jpg') }}" />
                <x-ui.avatar :name="$inst" size="xl" square />
            </div>

            <x-ui.dropdown id="g-dropdown" label="Aksi lainnya">
                <ul class="sg-menu__list">
                    <li><x-ui.menu-item href="#" icon="pencil">Ubah</x-ui.menu-item></li>
                    <li><x-ui.menu-item href="#" icon="copy">Duplikat</x-ui.menu-item></li>
                </ul>
                <hr class="sg-menu__sep">
                <x-ui.menu-item icon="trash-2" danger>Hapus</x-ui.menu-item>
            </x-ui.dropdown>

            <div class="grid gap-6 sm:grid-cols-2">
                <x-ui.empty-state illustration="no-applications" title="Belum ada lamaran" description="Telusuri masalah dari instansi dan ajukan lamaran pertama Anda.">
                    <x-slot:action><x-ui.button href="#">Telusuri Masalah</x-ui.button></x-slot:action>
                </x-ui.empty-state>
                <x-ui.empty-state illustration="no-search-results" title="Tidak ada hasil" description="Ubah kata kunci atau atur ulang filter." />
                <x-ui.empty-state illustration="no-documents" title="Belum ada dokumen" />
                <x-ui.empty-state illustration="no-saved-projects" title="Wishlist kosong" />
                <x-ui.empty-state illustration="no-notifications" title="Belum ada notifikasi" />
            </div>
        </section>
    </div>
</div>
@endsection
