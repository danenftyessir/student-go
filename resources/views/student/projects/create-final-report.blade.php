{{-- resources/views/student/projects/create-final-report.blade.php --}}
@extends('layouts.app')

@section('title', 'Kirim Laporan Akhir')

{{-- phase 29 paket B: formulir bersection. kontrak tetap: POST student.projects.store-final-report, multipart,
     field summary/activities/beneficiaries/activities_count/final_report (id finalReportUpload, .pdf, maks. 20 MB),
     tiga checkbox konfirmasi wajib tanpa name. konfirmasi sebelum kirim lewat dialog bersama (pengganti confirm()). --}}

@section('content')
<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Kirim laporan akhir" description="Selesaikan proyek dengan mengirim laporan akhir ke instansi mitra."
                      :breadcrumbs="['Proyek Saya' => route('student.projects.index'), Str::limit($project->title, 30) => route('student.projects.show', $project->id), 'Laporan akhir' => null]" />

    <x-ui.alert tone="warning" title="Sebelum mengirim" class="sg-inst-page__errors">
        <ul class="sg-sp-list">
            <li>Setelah laporan akhir dikirim, proyek ditandai <strong>selesai</strong>.</li>
            <li>Pastikan semua milestone telah diselesaikan.</li>
            <li>Berkas laporan harus dalam format PDF.</li>
            <li>Laporan akhir akan ditinjau oleh instansi mitra.</li>
        </ul>
    </x-ui.alert>

    <x-ui.form-errors class="sg-inst-page__errors" />

    <form action="{{ route('student.projects.store-final-report', $project->id) }}" method="POST" enctype="multipart/form-data"
          data-sg-confirm="Proyek akan ditandai selesai dan laporan akhir tidak dapat diubah lagi."
          data-sg-confirm-title="Kirim laporan akhir?" data-sg-confirm-label="Kirim dan selesaikan" data-sg-confirm-tone="primary">
        @csrf

        {{-- 1. isi laporan --}}
        <section class="sg-inst-section" aria-labelledby="judul-isi">
            <h2 id="judul-isi"><span class="sg-inst-section__num">1</span>Isi laporan</h2>

            <x-ui.field label="Ringkasan eksekutif" for="summary" required
                        help="Ringkasan singkat tentang proyek, tujuan, kegiatan yang dilakukan, dan hasil yang dicapai.">
                <x-ui.textarea name="summary" rows="6" required help placeholder="Tuliskan ringkasan eksekutif proyek Anda..." />
            </x-ui.field>
            <x-ui.field label="Deskripsi kegiatan lengkap" for="activities" required
                        help="Jelaskan secara detail semua kegiatan yang telah dilakukan selama proyek.">
                <x-ui.textarea name="activities" rows="8" required help placeholder="Jelaskan kegiatan yang telah dilakukan, metodologi, pendekatan, dan proses pelaksanaan..." />
            </x-ui.field>
        </section>

        {{-- 2. dampak --}}
        <section class="sg-inst-section" aria-labelledby="judul-dampak">
            <h2 id="judul-dampak"><span class="sg-inst-section__num">2</span>Dampak proyek</h2>
            <p class="sg-inst-detail__note">Data jumlah tentang dampak proyek Anda.</p>

            <div class="sg-inst-grid-2">
                <x-ui.field label="Jumlah penerima manfaat" for="beneficiaries" help="Berapa orang yang terdampak langsung dari proyek ini?">
                    <x-ui.input type="number" name="beneficiaries" :value="0" min="0" help placeholder="Contoh: 100" />
                </x-ui.field>
                <x-ui.field label="Jumlah kegiatan terlaksana" for="activities_count" help="Berapa kegiatan yang telah dilaksanakan?">
                    <x-ui.input type="number" name="activities_count" :value="0" min="0" help placeholder="Contoh: 15" />
                </x-ui.field>
            </div>
        </section>

        {{-- 3. berkas dan konfirmasi --}}
        <section class="sg-inst-section" aria-labelledby="judul-berkas">
            <h2 id="judul-berkas"><span class="sg-inst-section__num">3</span>Berkas dan konfirmasi</h2>

            <x-ui.upload name="final_report" id="finalReportUpload" label="Berkas laporan akhir" accept=".pdf" required
                         hint="PDF maksimal 20MB" :max-size="20 * 1024 * 1024" />

            <fieldset class="sg-fieldset">
                <legend class="sg-label" data-required>Konfirmasi</legend>
                <label class="sg-check">
                    <input type="checkbox" required>
                    <span>Saya menyatakan bahwa semua informasi yang diberikan adalah benar dan akurat</span>
                </label>
                <label class="sg-check">
                    <input type="checkbox" required>
                    <span>Laporan ini adalah hasil kerja saya sendiri dan tidak melanggar hak cipta</span>
                </label>
                <label class="sg-check">
                    <input type="checkbox" required>
                    <span>Saya memahami bahwa laporan akan ditinjau oleh instansi mitra</span>
                </label>
            </fieldset>
        </section>

        <div class="sg-inst-savebar">
            <x-ui.button variant="tertiary" :href="route('student.projects.show', $project->id)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="send">Kirim laporan akhir dan selesaikan proyek</x-ui.button>
        </div>
    </form>
</div>
@endsection
