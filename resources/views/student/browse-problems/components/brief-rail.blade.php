{{-- rail lamar (phase 9). urutan keadaan:
     1. sudah ada lamaran -> status asli dari tabel applications (Menunggu / Ditinjau / Diterima / Ditolak, StatusVocabulary D-02)
     2. masalah tidak berstatus open -> tidak menerima lamaran (sama dengan ApplicationController@create)
     3. application_deadline < now() -> tenggat lewat (predikat yang sama dengan create)
     4. selain itu -> tombol lamar ke student.applications.create.
     kuota terisi hanya informasi (jumlah lamaran diterima >= kuota); create tidak memeriksa kuota, jadi tombol tidak disembunyikan.
     tidak ada tombol batalkan di sini (phase 10). --}}
@php
    $appliedMessages = [
        'pending' => 'Lamaran Anda sudah terkirim dan menunggu ditinjau instansi.',
        'reviewed' => 'Instansi sedang meninjau lamaran Anda.',
        'accepted' => 'Lamaran Anda diterima instansi.',
        'rejected' => 'Lamaran Anda untuk masalah ini tidak diterima.',
    ];
    $state = $application ? 'applied' : ($problem->status !== 'open' ? 'closed' : ($deadlinePassed ? 'expired' : 'open'));
@endphp
<aside class="sg-brief__rail" aria-labelledby="brief-rail-title">
    <div class="sg-brief-rail" data-state="{{ $state }}" @if ($application) data-application-status="{{ $application->status }}" @endif>
        <h2 id="brief-rail-title" class="sg-brief-rail__title">Lamaran</h2>

        <div class="sg-brief-rail__state">
            @if ($state === 'applied')
                <x-ui.status domain="application" :value="$application->status" />
                <p class="sg-brief-rail__text">{{ $appliedMessages[$application->status] ?? 'Anda sudah melamar masalah ini.' }}</p>
                @if ($application->applied_at)
                    <p class="sg-brief-rail__meta">Dikirim {{ $fmtDate($application->applied_at) }}</p>
                @endif
                <x-ui.button variant="secondary" block :href="route('student.applications.show', $application->id)">Lihat lamaran</x-ui.button>
            @elseif ($state === 'closed')
                <x-ui.status domain="problem" :value="$problem->status" />
                <p class="sg-brief-rail__text">Masalah ini tidak menerima lamaran.</p>
            @elseif ($state === 'expired')
                <p class="sg-brief-rail__notice"><x-icon name="lock" size="sm" />Pendaftaran ditutup</p>
                <p class="sg-brief-rail__text">Tenggat lamaran {{ $fmtDate($deadline) }} sudah lewat.</p>
            @else
                <p class="sg-brief-rail__text">
                    Lamaran dibuka sampai <b>{{ $fmtDate($deadline) }}</b>
                    ({{ $daysLeft > 1 ? $daysLeft . ' hari lagi' : 'besok' }}).
                </p>
                <x-ui.button variant="primary" block :href="route('student.applications.create', $problem->id)" icon-end="arrow-right">Lamar masalah ini</x-ui.button>
            @endif

            @if ($quotaFilled && $state !== 'applied')
                <p class="sg-brief-rail__meta"><x-icon name="info" size="sm" />Kuota sudah terisi: {{ $acceptedCount }} dari {{ $problem->required_students }} mahasiswa diterima.</p>
            @endif
        </div>

        <dl class="sg-dl sg-brief-rail__facts">
            <div><dt>Instansi</dt><dd>{{ $institution->name ?? '—' }}</dd></div>
            <div><dt>Lokasi proyek</dt><dd>{{ $regionPlace ?: '—' }}</dd></div>
            @if ($deadline)
                <div>
                    <dt>Tenggat lamaran</dt>
                    <dd><time datetime="{{ $deadline->toDateString() }}">{{ $fmtDate($deadline) }}</time></dd>
                </div>
            @endif
            @if ($problem->start_date)
                <div><dt>Mulai</dt><dd><time datetime="{{ $problem->start_date->toDateString() }}">{{ $fmtDate($problem->start_date) }}</time></dd></div>
            @endif
            @if ($problem->duration_months)
                <div><dt>Durasi</dt><dd>{{ $problem->duration_months }} bulan</dd></div>
            @endif
            <div><dt>Kuota</dt><dd><span class="sg-brief-num">{{ $problem->required_students }}</span> mahasiswa · <span class="sg-brief-num">{{ $acceptedCount }}</span> diterima</dd></div>
            <div><dt>Dilihat</dt><dd><span class="sg-brief-num">{{ number_format((int) $problem->views_count, 0, ',', '.') }}</span> kali</dd></div>
        </dl>
    </div>
</aside>
