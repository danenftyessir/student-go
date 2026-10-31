@extends('layouts.app')

@section('title', 'Upload Dokumen Verifikasi')

{{-- phase 20: checklist dokumen + komponen unggah bersama (J17, J21). kontrak tetap: POST verification.upload.submit
     (multipart) dengan official_letter / logo / pic_identity / npwp, jenis dan ukuran berkas sama dengan validasi
     controller; POST verification.trigger tanpa field. status yang tampil hanya yang disimpan backend: satu status
     instansi (verification_status) dan hasil per dokumen (verification_documents.ai_status, atau human_status
     bila sudah ditinjau). copy tidak menjanjikan metode atau durasi pemeriksaan (D-09 masih terbuka). --}}

@php
    // urutan, jenis berkas dan batas ukuran mengikuti VerificationController::uploadDocuments
    $documents = [
        'official_letter' => ['title' => 'Surat pengantar atau SK instansi', 'req' => 'Wajib',
            'desc' => 'Surat resmi dari instansi.', 'accept' => 'application/pdf', 'hint' => 'PDF, maks. 5 MB', 'max' => 5 * 1024 * 1024],
        'logo' => ['title' => 'Logo instansi', 'req' => null,
            'desc' => 'Logo resmi instansi.', 'accept' => 'image/jpeg,image/png', 'hint' => 'JPG atau PNG, maks. 2 MB', 'max' => 2 * 1024 * 1024],
        'pic_identity' => ['title' => 'KTP penanggung jawab', 'req' => null,
            'desc' => 'Foto KTP penanggung jawab yang terdaftar di profil.', 'accept' => 'image/jpeg,image/png', 'hint' => 'JPG atau PNG, maks. 2 MB', 'max' => 2 * 1024 * 1024],
        'npwp' => ['title' => 'NPWP instansi', 'req' => 'Opsional',
            'desc' => 'Kartu atau surat keterangan NPWP instansi.', 'accept' => 'application/pdf,image/jpeg,image/png', 'hint' => 'PDF, JPG atau PNG, maks. 2 MB', 'max' => 2 * 1024 * 1024],
    ];
    $byType = $existingDocuments->keyBy('document_type');
    $uploadedCount = collect(array_keys($documents))->filter(fn ($type) => $byType->has($type))->count();
    $status = $institution->verification_status;
    $statusText = [
        'pending_verification' => 'Dokumen instansi belum diperiksa. Unggah dokumen di bawah, lalu ajukan pemeriksaan.',
        'needs_review' => 'Sebagian dokumen perlu ditinjau lebih lanjut. Periksa catatan di halaman status verifikasi.',
        'rejected' => 'Verifikasi ditolak. Lihat alasannya di halaman status verifikasi, lalu unggah ulang dokumen yang diperlukan.',
        'active' => 'Instansi sudah terverifikasi.',
        'suspended' => 'Akun instansi sedang ditangguhkan.',
    ][$status] ?? null;
    $bytes = fn ($size) => $size >= 1048576 ? number_format($size / 1048576, 1, ',', '.') . ' MB' : number_format(max(1, round($size / 1024)), 0, ',', '.') . ' KB';
@endphp

@section('content')
<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Verifikasi dokumen instansi"
                      description="Unggah dokumen resmi instansi. Hasil pemeriksaan tampil per dokumen di halaman ini."
                      :back="route('institution.dashboard')" back-label="Kembali ke dashboard" />

    @if (session('success'))
        <x-ui.alert tone="success" class="sg-inst-page__errors">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert tone="danger" class="sg-inst-page__errors">{{ session('error') }}</x-ui.alert>
    @endif
    <x-ui.form-errors class="sg-inst-page__errors" />

    <div class="sg-inst-verify">
        {{-- satu status instansi --}}
        <section class="sg-inst-panel" aria-labelledby="verifikasi-status">
            <h2 id="verifikasi-status"><x-icon name="shield-check" />Status verifikasi</h2>
            <div class="sg-inst-panel__row">
                <x-ui.status domain="verification" :value="$status" />
                @if ($institution->verification_score !== null)
                    <span class="sg-inst-checklist__date">Skor pemeriksaan {{ rtrim(rtrim(number_format((float) $institution->verification_score, 2, ',', '.'), '0'), ',') }}/100</span>
                @endif
            </div>
            @if ($statusText)<p>{{ $statusText }}</p>@endif
        </section>

        {{-- checklist dokumen --}}
        <form action="{{ route('institution.verification.upload.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="sg-inst-checklist__head">
                <h2 id="verifikasi-dokumen">Dokumen</h2>
                <span class="sg-inst-checklist__count">{{ $uploadedCount }} dari {{ count($documents) }} dokumen diunggah</span>
            </div>

            <ol class="sg-inst-checklist" aria-labelledby="verifikasi-dokumen">
                @foreach ($documents as $type => $doc)
                    @php
                        $stored = $byType->get($type);
                        $result = $stored ? ($stored->human_reviewed && filled($stored->human_status) ? $stored->human_status : $stored->ai_status) : null;
                    @endphp
                    <li>
                        <span class="sg-inst-checklist__mark" @if ($stored) data-done @endif>
                            <x-icon :name="$stored ? 'check' : 'circle-dashed'" />
                        </span>
                        <div class="sg-inst-checklist__body">
                            <div>
                                <h3 class="sg-inst-checklist__title">
                                    {{ $doc['title'] }}
                                    @if ($doc['req'])<span class="sg-inst-checklist__req">({{ strtolower($doc['req']) }})</span>@endif
                                </h3>
                                <p class="sg-inst-checklist__desc">{{ $doc['desc'] }}</p>
                            </div>

                            <p class="sg-inst-checklist__state">
                                @if ($stored)
                                    <span class="sg-file-chip">
                                        <x-icon name="file-text" size="sm" />
                                        <span class="sg-file-chip__name" title="{{ $stored->file_name }}">{{ $stored->file_name }}</span>
                                        @if ($stored->file_size)<span class="sg-file-chip__meta">{{ $bytes($stored->file_size) }}</span>@endif
                                    </span>
                                    @if ($result)
                                        <x-ui.status domain="verification_document" :value="$result" />
                                    @else
                                        <span>Diunggah, belum diperiksa</span>
                                    @endif
                                    <span class="sg-inst-checklist__date">{{ $stored->updated_at?->locale('id')->translatedFormat('j M Y, H.i') }}</span>
                                @else
                                    <span>Belum diunggah</span>
                                @endif
                            </p>

                            <x-ui.upload :name="$type" :label="$stored ? 'Ganti berkas' : 'Unggah berkas'" :accept="$doc['accept']"
                                         :hint="$doc['hint']" :max-size="$doc['max']" />
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="sg-inst-verify__actions">
                <x-ui.button type="submit" icon="upload">Upload Dokumen</x-ui.button>
                <x-ui.button variant="tertiary" :href="route('institution.dashboard')">Kembali ke Dashboard</x-ui.button>
            </div>
        </form>

        {{-- ajukan pemeriksaan: form dan route sama (verification.trigger) --}}
        @if ($existingDocuments->isNotEmpty())
            <section class="sg-inst-panel" aria-labelledby="verifikasi-ajukan">
                <h2 id="verifikasi-ajukan"><x-icon name="clipboard-check" />Ajukan pemeriksaan dokumen</h2>
                <p>Ajukan setelah dokumen yang diperlukan sudah diunggah. Hasil per dokumen dan status instansi tampil di halaman status verifikasi.</p>
                <div class="sg-inst-panel__actions">
                    <form action="{{ route('institution.verification.trigger') }}" method="POST">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" icon="send">Ajukan pemeriksaan</x-ui.button>
                    </form>
                    <x-ui.button variant="tertiary" :href="route('institution.verification.status')">Lihat Status Verifikasi</x-ui.button>
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
