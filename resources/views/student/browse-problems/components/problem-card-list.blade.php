{{-- satu baris tabel daftar masalah (phase 8). semua nilai dari kolom/relasi problem; status lamaran hanya bila mahasiswa sudah melamar. --}}
@php
    $level = $difficulties[$problem->difficulty_level] ?? null;
    $sdgs = array_values(array_filter(array_map('intval', (array) $problem->sdg_categories)));
    $deadline = $problem->application_deadline;
    $daysLeft = $deadline ? (int) today()->diffInDays($deadline, false) : null;
    $applied = $applicationStatuses[$problem->id] ?? null;
@endphp
<tr data-problem-id="{{ $problem->id }}">
    <td data-label="Masalah">
        <div class="sg-browse-row">
            <a href="{{ route('student.browse-problems.show', $problem->id) }}" class="sg-browse-row__title">{{ $problem->title }}</a>
            @if ($applied || $problem->is_urgent || $problem->is_featured)
                <p class="sg-browse-row__flags">
                    @if ($applied)<x-ui.status domain="application" :value="$applied" />@endif
                    @if ($problem->is_urgent)<span class="sg-browse-flag"><x-icon name="clock" size="sm" />Mendesak</span>@endif
                    @if ($problem->is_featured)<span class="sg-browse-flag"><x-icon name="award" size="sm" />Unggulan</span>@endif
                </p>
            @endif
            @if (count($sdgs))
                <ul class="sg-browse-row__sdgs" aria-label="Tujuan SDG">
                    @foreach ($sdgs as $sdg)
                        <li><x-ui.chip>SDG {{ $sdg }} · {{ \App\Http\Controllers\HomeController::sdgName($sdg) }}</x-ui.chip></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </td>
    <td data-label="Instansi"><span>{{ optional($problem->institution)->name ?? '—' }}</span></td>
    <td data-label="Lokasi">
        <span>{{ optional($problem->regency)->name ?? '—' }}<span class="sg-table__meta">{{ optional($problem->province)->name }}</span></span>
    </td>
    <td data-label="Kuota"><span><span class="sg-browse-num">{{ $problem->required_students }}</span><span class="sg-table__meta">mahasiswa</span></span></td>
    <td data-label="Tenggat">
        @if ($deadline)
            <span>
                <time datetime="{{ $deadline->toDateString() }}" class="sg-browse-num">{{ $deadline->locale('id')->translatedFormat('j M Y') }}</time>
                <span class="sg-table__meta">{{ $daysLeft > 1 ? $daysLeft . ' hari lagi' : ($daysLeft === 1 ? 'besok' : ($daysLeft === 0 ? 'hari ini' : 'sudah lewat')) }}</span>
            </span>
        @else
            <span>—</span>
        @endif
    </td>
    <td data-label="Kesulitan">
        @if ($level)
            <span class="sg-meter" data-level="{{ $level[0] }}"><span class="sg-meter__bars" aria-hidden="true"><i></i><i></i><i></i></span>{{ $level[1] }}</span>
        @else
            <span>—</span>
        @endif
    </td>
</tr>
