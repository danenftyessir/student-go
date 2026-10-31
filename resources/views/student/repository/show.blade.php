{{-- resources/views/student/repository/show.blade.php --}}
@extends('layouts.app')

@section('title', $document->title)

{{-- phase 14: detail dokumen sebagai halaman katalog perpustakaan. judul, abstrak (kolom baca serif), metadata
     sebagai definition list, unduh lewat endpoint lama (student.repository.download), dokumen terkait tanpa duplikat.
     tanpa pratinjau PDF (fitur baru, D-26 masih OPEN). --}}

@use('App\Support\RepositoryFormat', 'Fmt')

@php
    $sdgs = Fmt::sdgs($document->categories);
    $fileType = Fmt::fileType($document->file_type);
    $fileSize = Fmt::fileSize($document->file_size);
    $abstract = trim((string) $document->description);
@endphp

@section('content')
<div class="sg-container sg-repo sg-repo-doc">
    <x-ui.page-header
        :title="$document->title"
        :breadcrumbs="['Repositori' => route('student.repository.index'), Str::limit($document->title, 50) => null]"
        class="sg-repo-doc__head">
        <x-slot:actions>
            <div class="sg-repo-doc__actions flex gap-2">
                <button type="button" class="sg-btn sg-btn--primary"
                        id="download-btn-{{ $document->id }}"
                        data-download-url="{{ route('student.repository.download', $document->id) }}"
                        data-title="{{ $document->title }}"
                        onclick="downloadDocument(this)">
                    <x-icon name="download" size="sm" />
                    <span class="download-text">Unduh{{ $fileType ? ' ' . $fileType : '' }}</span>
                </button>
                @if ($document->file_path)
                    <a href="{{ document_url($document->file_path) }}" target="_blank" rel="noopener" class="sg-btn sg-btn--secondary">
                        <x-icon name="external-link" size="sm" />Buka di tab baru
                    </a>
                @endif
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- urutan di layar kecil: abstrak, informasi dokumen, terkait; mulai 1024px informasi di kolom kanan --}}
    <div class="sg-repo-doc__layout">
        <section class="sg-repo-doc__section sg-repo-doc__abstract-section" aria-labelledby="abstrak">
            <h2 id="abstrak">Abstrak</h2>
            @if ($abstract !== '')
                <p class="sg-prose sg-repo-doc__abstract">{{ $abstract }}</p>
            @else
                <p class="sg-repo-doc__none">Dokumen ini belum memiliki abstrak.</p>
            @endif
            @if (count($sdgs))
                <ul class="sg-repo-doc__sdgs" aria-label="Tujuan SDG">
                    @foreach ($sdgs as $sdg)
                        <li><span class="sg-chip sg-sdg" data-sdg="{{ $sdg['id'] }}"><span class="sg-chip__swatch" aria-hidden="true"></span>SDG {{ $sdg['id'] }} · {{ $sdg['name'] }}</span></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="sg-panel sg-repo-doc__record" aria-labelledby="info-dokumen">
            <h2 id="info-dokumen">Informasi dokumen</h2>
            <x-ui.dl :items="[
                'Penulis' => $document->author_name,
                'Instansi' => $document->institution_name,
                'Universitas' => $document->university_name,
                'Provinsi' => optional($document->province)->name,
                'Kabupaten/Kota' => optional($document->regency)->name,
                'Tahun' => $document->year,
                'Format' => $fileType,
                'Ukuran' => $fileSize,
            ]">
                <div>
                    <dt>Dilihat</dt>
                    <dd class="sg-num">{{ Fmt::number($document->view_count) }} kali</dd>
                </div>
                <div>
                    <dt>Diunduh</dt>
                    <dd class="sg-num">{{ Fmt::number($document->download_count) }} kali</dd>
                </div>
            </x-ui.dl>
        </aside>

        @if ($relatedDocuments->count() > 0)
            <section class="sg-repo-doc__section sg-repo-related" aria-labelledby="terkait">
                <h2 id="terkait">Dokumen terkait</h2>
                <ul class="sg-list">
                    @foreach ($relatedDocuments as $related)
                        <li data-related-id="{{ $related->id }}">
                            <a href="{{ route('student.repository.show', $related->id) }}">{{ $related->title }}</a>
                            <p class="sg-repo-related__meta">
                                {{ collect([$related->institution_name, optional($related->province)->name, $related->year])->filter()->implode(' · ') }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// unduh tanpa memuat ulang halaman; endpoint lama tetap menaikkan hitungan unduhan
async function downloadDocument(button) {
    const label = button.querySelector('.download-text');
    const original = label.textContent;
    button.disabled = true;
    label.textContent = 'Mengunduh…';

    try {
        const response = await fetch(button.dataset.downloadUrl, {
            method: 'GET',
            headers: { 'Accept': 'application/octet-stream', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) {
            throw new Error('Download failed');
        }

        // nama file dari header Content-Disposition, cadangan: judul dokumen
        let filename = `${button.dataset.title}.pdf`;
        const disposition = response.headers.get('Content-Disposition');
        const match = disposition ? disposition.match(/filename="?([^"]+)"?/i) : null;
        if (match && match[1]) {
            filename = match[1];
        }

        const url = window.URL.createObjectURL(await response.blob());
        const a = document.createElement('a');
        a.hidden = true;
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();

        window.showNotification?.('Dokumen berhasil diunduh.', 'success');
    } catch (error) {
        console.error('Download error:', error);
        window.showNotification?.('Gagal mengunduh dokumen. Silakan coba lagi.', 'error');
    } finally {
        button.disabled = false;
        label.textContent = original;
    }
}
</script>
@endpush
