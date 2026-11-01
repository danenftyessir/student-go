{{-- resources/views/student/projects/create-report.blade.php --}}
@extends('layouts.app')

@section('title', 'Buat Laporan Kemajuan')

{{-- phase 29 paket B: formulir bersection. kontrak tetap: POST student.projects.store-report, multipart,
     field type/title/period_start/period_end/summary/activities/challenges/next_plans/document/photos[],
     id unggahan documentUpload dan photosUpload, accept dan batas ukuran sama dengan validasi controller. --}}

@section('content')
<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Buat laporan kemajuan" description="Catat kegiatan, kendala dan rencana untuk satu periode proyek."
                      :breadcrumbs="['Proyek Saya' => route('student.projects.index'), Str::limit($project->title, 30) => route('student.projects.show', $project->id), 'Buat laporan' => null]" />

    <x-ui.form-errors class="sg-inst-page__errors" />

    <form action="{{ route('student.projects.store-report', $project->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- 1. periode --}}
        <section class="sg-inst-section" aria-labelledby="judul-periode">
            <h2 id="judul-periode"><span class="sg-inst-section__num">1</span>Jenis dan periode</h2>

            <div class="sg-inst-grid-2">
                <x-ui.field label="Jenis laporan" for="type" required>
                    <x-ui.select name="type" placeholder="Pilih jenis laporan" required
                                 :options="['weekly' => 'Laporan Mingguan', 'monthly' => 'Laporan Bulanan']" />
                </x-ui.field>
                <x-ui.field label="Judul laporan" for="title" required>
                    <x-ui.input name="title" required placeholder="Contoh: Laporan Mingguan - Minggu ke-1" />
                </x-ui.field>
            </div>

            <div class="sg-inst-grid-2">
                <x-ui.field label="Periode mulai" for="period_start" required>
                    <x-ui.input type="date" name="period_start" required />
                </x-ui.field>
                <x-ui.field label="Periode berakhir" for="period_end" required>
                    <x-ui.input type="date" name="period_end" required />
                </x-ui.field>
            </div>
        </section>

        {{-- 2. isi laporan --}}
        <section class="sg-inst-section" aria-labelledby="judul-isi">
            <h2 id="judul-isi"><span class="sg-inst-section__num">2</span>Isi laporan</h2>

            <x-ui.field label="Ringkasan kegiatan" for="summary" required>
                <x-ui.textarea name="summary" rows="4" required placeholder="Tuliskan ringkasan singkat tentang kegiatan yang telah dilakukan..." />
            </x-ui.field>
            <x-ui.field label="Detail kegiatan" for="activities" required>
                <x-ui.textarea name="activities" rows="6" required placeholder="Jelaskan secara detail kegiatan yang telah dilakukan, metodologi, dan hasil yang dicapai..." />
            </x-ui.field>
            <x-ui.field label="Kendala atau tantangan" for="challenges">
                <x-ui.textarea name="challenges" rows="4" placeholder="Jelaskan kendala atau tantangan yang dihadapi (jika ada)..." />
            </x-ui.field>
            <x-ui.field label="Rencana selanjutnya" for="next_plans">
                <x-ui.textarea name="next_plans" rows="4" placeholder="Jelaskan rencana kegiatan untuk periode berikutnya..." />
            </x-ui.field>
        </section>

        {{-- 3. lampiran --}}
        <section class="sg-inst-section" aria-labelledby="judul-lampiran">
            <h2 id="judul-lampiran"><span class="sg-inst-section__num">3</span>Lampiran <span class="sg-inst-section__opt">opsional</span></h2>

            <x-ui.upload name="document" id="documentUpload" label="Dokumen laporan" accept=".pdf,.doc,.docx"
                         hint="PDF, DOC, DOCX maksimal 10MB" :max-size="10 * 1024 * 1024" />
            <x-ui.upload name="photos[]" id="photosUpload" label="Foto dokumentasi" accept="image/*" multiple
                         hint="JPG, PNG maksimal 5MB per foto (bisa lebih dari satu)" :max-size="5 * 1024 * 1024" />
        </section>

        <div class="sg-inst-savebar">
            <x-ui.button variant="tertiary" :href="route('student.projects.show', $project->id)">Batal</x-ui.button>
            <x-ui.button type="submit" icon="send">Kirim laporan</x-ui.button>
        </div>
    </form>
</div>
@endsection
