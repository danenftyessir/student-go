{{-- phase 19: detail satu lamaran. context = panel (kolom kanan split view, nama sebagai h2) | page (halaman /applications/{id},
     nama sudah jadi h1 di page header). kontak mahasiswa (email/whatsapp) tidak ditampilkan untuk instansi (D-04);
     yang boleh dilihat: kampus, jurusan, semester, isi lamaran, dan profil publik (yang menegakkan D-04 sendiri). --}}
@php
    $student = $application->student;
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j F Y, H.i') : null;
    $decidable = in_array($application->status, ['pending', 'reviewed'], true);
    $prefix = 'lamaran-'.$application->id;
    // tingkat judul bagian: h3 di panel (di bawah h2 nama), h2 di halaman (di bawah h1 page header)
    $h = $context === 'panel' ? 'h3' : 'h2';
    $subline = collect([
        $student?->university?->name,
        $student?->major,
        $student?->semester ? 'Semester '.$student->semester : null,
    ])->filter()->implode(' · ');
    $sizeKb = $application->proposal_size ? number_format($application->proposal_size / 1024, 0, ',', '.').' KB' : null;
@endphp

<article class="sg-inst-app" @if ($context === 'panel') aria-labelledby="{{ $prefix }}-nama" @else aria-label="Detail lamaran" @endif>
    @if ($context === 'panel')
        <div class="sg-inst-app__head">
            <x-ui.avatar :name="$student?->display_name" size="lg" decorative />
            <div class="sg-inst-app__who">
                <h2 id="{{ $prefix }}-nama" tabindex="-1">{{ $student?->display_name }}</h2>
                @if ($subline !== '')<p class="sg-inst-app__sub">{{ $subline }}</p>@endif
            </div>
            <x-ui.status domain="application" :value="$application->status" />
        </div>
    @endif

    {{-- bar keputusan: Terima (utama) dan Tolak (outline bahaya) hanya untuk lamaran yang belum diputuskan --}}
    <div class="sg-inst-decision">
        @if ($decidable && $context === 'inline')
            <p class="sg-inst-decision__text">Lamaran ini menunggu keputusan Anda. Isi salah satu formulir di bawah.</p>
        @elseif ($decidable)
            <p class="sg-inst-decision__text">Lamaran ini menunggu keputusan Anda.</p>
            <div class="sg-inst-decision__actions">
                <x-ui.button data-sg-open="{{ $prefix }}-terima" icon="check">Terima</x-ui.button>
                <x-ui.button variant="danger" data-sg-open="{{ $prefix }}-tolak" icon="x">Tolak</x-ui.button>
            </div>
        @else
            <p class="sg-inst-decision__text">
                Keputusan: <x-ui.status domain="application" :value="$application->status" />
                @if ($application->reviewed_at) <span>pada {{ $tanggal($application->reviewed_at) }}</span>@endif
            </p>
        @endif
    </div>

    <div class="sg-inst-app__grid">
        <div class="sg-inst-app__main">
            <section class="sg-inst-detail__block">
                <{{ $h }} class="sg-inst-detail__rail-h">Masalah yang dilamar</{{ $h }}>
                <p class="sg-inst-detail__note">
                    <a href="{{ route('institution.problems.show', $application->problem_id) }}" class="sg-inst-link">{{ $application->problem?->title }}</a>
                </p>
            </section>

            <section class="sg-inst-detail__block">
                <{{ $h }} class="sg-inst-detail__rail-h">Motivasi</{{ $h }}>
                @if (filled($application->motivation))
                    <div class="sg-inst-detail__text">{!! nl2br(e($application->motivation)) !!}</div>
                @else
                    <p class="sg-inst-detail__note">Mahasiswa tidak menulis motivasi.</p>
                @endif
            </section>

            @if (filled($application->cover_letter))
                <section class="sg-inst-detail__block">
                    <{{ $h }} class="sg-inst-detail__rail-h">Surat pengantar</{{ $h }}>
                    <div class="sg-inst-detail__text">{!! nl2br(e($application->cover_letter)) !!}</div>
                </section>
            @endif

            <section class="sg-inst-detail__block">
                <{{ $h }} class="sg-inst-detail__rail-h">Proposal</{{ $h }}>
                @if ($application->proposal_content)
                    <p class="sg-inst-file">
                        <x-icon name="file-text" size="sm" />
                        <span>{{ $application->proposal_filename ?: 'Proposal' }}@if ($sizeKb) · {{ $sizeKb }}@endif</span>
                        <a href="{{ route('institution.applications.download-proposal', $application->id) }}" class="sg-inst-link" target="_blank" rel="noopener">Buka proposal</a>
                    </p>
                @else
                    <p class="sg-inst-detail__note">Tidak ada proposal yang diunggah.</p>
                @endif
            </section>

            @if (filled($application->feedback))
                <section class="sg-inst-detail__block">
                    <{{ $h }} class="sg-inst-detail__rail-h">Masukan untuk mahasiswa</{{ $h }}>
                    <div class="sg-inst-detail__text">{!! nl2br(e($application->feedback)) !!}</div>
                </section>
            @endif

            @if (filled($application->institution_notes))
                <section class="sg-inst-detail__block">
                    <{{ $h }} class="sg-inst-detail__rail-h">Catatan internal</{{ $h }}>
                    <div class="sg-inst-detail__text">{!! nl2br(e($application->institution_notes)) !!}</div>
                </section>
            @endif
        </div>

        <aside class="sg-inst-app__aside" aria-label="Ringkasan lamaran">
            <x-ui.dl :items="array_filter([
                'Kampus' => $student?->university?->name,
                'Jurusan' => $student?->major,
                'Semester' => $student?->semester,
                'Dikirim' => $tanggal($application->applied_at ?? $application->created_at),
                'Diputuskan' => $tanggal($application->reviewed_at),
            ])" />
            @if ($student?->user?->username)
                <a href="{{ route('profile.public', $student->user->username) }}" class="sg-inst-link">Lihat profil publik<span class="sr-only"> {{ $student->display_name }}</span></a>
            @endif
        </aside>
    </div>

    @if ($decidable && $context !== 'inline')
        @include('institution.applications.components.decision-forms', ['application' => $application, 'modal' => true])
    @endif
</article>
