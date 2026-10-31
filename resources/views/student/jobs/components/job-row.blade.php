{{-- satu baris tabel lowongan (phase 15), dipakai halaman lowongan dan lowongan tersimpan.
     kolom Posisi · Perusahaan · Lokasi · SDG · Tenggat; aksi (simpan/bandingkan atau ubah/hapus) di bawah judul posisi.
     pola dan kelas sama dengan daftar masalah (phase 8).
     semua nilai dari kolom/relasi job_postings; tenggat = expires_at. $saved (SavedJob) hanya di halaman tersimpan. --}}
@php
    $saved = $saved ?? null;
    $typeLabels = ['internship' => 'Magang', 'magang' => 'Magang', 'contract' => 'Kontrak', 'full_time' => 'Penuh waktu', 'part_time' => 'Paruh waktu', 'freelance' => 'Lepas'];
    $typeLabel = $job->job_type ? ($typeLabels[strtolower($job->job_type)] ?? $job->job_type) : null;

    // gaji: rupiah dalam juta (1–2 juta); mata uang lain apa adanya
    $salary = null;
    if ($job->salary_min || $job->salary_max) {
        if (($job->salary_currency ?? 'IDR') === 'IDR') {
            $juta = fn ($v) => rtrim(rtrim(number_format((float) $v / 1000000, 1, ',', '.'), '0'), ',');
            $salary = 'Rp ' . ($job->salary_min && $job->salary_max
                ? $juta($job->salary_min) . '–' . $juta($job->salary_max)
                : $juta($job->salary_min ?: $job->salary_max)) . ' juta';
        } else {
            $num = fn ($v) => number_format((float) $v, 0, ',', '.');
            $salary = $job->salary_currency . ' ' . ($job->salary_min && $job->salary_max
                ? $num($job->salary_min) . '–' . $num($job->salary_max)
                : $num($job->salary_min ?: $job->salary_max));
        }
    }

    $sdgs = array_values(array_unique(array_filter(array_map('intval', (array) $job->sdg_alignment), fn ($n) => $n >= 1 && $n <= 17)));
    $deadline = $job->expires_at;
    $daysLeft = $deadline ? (int) today()->diffInDays($deadline->copy()->startOfDay(), false) : null;
    $applied = in_array($job->id, $appliedJobIds ?? [], true);
@endphp
<tr data-job-id="{{ $job->id }}">
    <td data-label="Posisi">
        <div class="sg-browse-row">
            <a href="{{ route('student.jobs.show', $job->id) }}" class="sg-browse-row__title">{{ $job->title }}</a>
            @if ($typeLabel || $salary)
                <span class="sg-table__meta">{{ implode(' · ', array_filter([$typeLabel, $salary])) }}</span>
            @endif
            @if ($applied || $saved?->folder)
                <p class="sg-browse-row__flags">
                    @if ($applied)<span class="sg-browse-flag"><x-icon name="check" size="sm" />Sudah melamar</span>@endif
                    @if ($saved?->folder)<span class="sg-browse-flag"><x-icon name="folder-open" size="sm" />{{ $saved->folder }}</span>@endif
                </p>
            @endif
            @if ($saved?->notes)
                <span class="sg-table__meta">Catatan: {{ $saved->notes }}</span>
            @endif
            <div class="-ml-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                @if ($saved)
                    {{-- halaman tersimpan: ubah folder/catatan (PUT update-saved) dan hapus (POST toggle-save) --}}
                    <button type="button" class="sg-btn sg-btn--tertiary" @click="openEdit(@js([
                            'job_id' => $job->id,
                            'title' => $job->title,
                            'folder' => $saved->folder ?? '',
                            'notes' => $saved->notes ?? '',
                            'reminder_at' => optional($saved->reminder_at)->format('Y-m-d\TH:i') ?? '',
                        ]))">
                        <x-icon name="pencil" size="sm" />Ubah<span class="sr-only"> catatan: {{ $job->title }}</span>
                    </button>
                    <button type="button" class="sg-btn sg-btn--tertiary" @click="removeSaved({{ $job->id }}, @js($job->title))">
                        <x-icon name="trash-2" size="sm" />Hapus<span class="sr-only"> dari tersimpan: {{ $job->title }}</span>
                    </button>
                @else
                    {{-- halaman lowongan: simpan/batal simpan (POST toggle-save) dan pilih untuk dibandingkan (maks. 3) --}}
                    <button type="button" class="sg-btn sg-btn--tertiary" @click="toggleSave({{ $job->id }})">
                        <x-icon name="bookmark" size="sm" />
                        <span x-text="isSaved({{ $job->id }}) ? 'Batal simpan' : 'Simpan'">{{ in_array($job->id, $savedJobIds ?? [], true) ? 'Batal simpan' : 'Simpan' }}</span><span class="sr-only">: {{ $job->title }}</span>
                    </button>
                    <label class="sg-check py-1" for="compare-{{ $job->id }}">
                        <input type="checkbox" id="compare-{{ $job->id }}" :checked="compareList.includes({{ $job->id }})"
                               @change="toggleCompare({{ $job->id }}, $event.target)"
                               :disabled="!compareList.includes({{ $job->id }}) && compareList.length >= 3">
                        <span>Bandingkan<span class="sr-only">: {{ $job->title }}</span></span>
                    </label>
                @endif
            </div>
        </div>
    </td>
    <td data-label="Perusahaan"><span>{{ optional($job->company)->name ?? '—' }}</span></td>
    <td data-label="Lokasi"><span>{{ $job->location ?: '—' }}</span></td>
    <td data-label="SDG">
        @if (count($sdgs))
            <ul class="sg-browse-row__sdgs" aria-label="Tujuan SDG">
                @foreach ($sdgs as $sdg)
                    <li><span class="sg-chip sg-sdg" data-sdg="{{ $sdg }}"><span class="sg-chip__swatch" aria-hidden="true"></span>SDG {{ $sdg }} · {{ \App\Http\Controllers\HomeController::sdgName($sdg) }}</span></li>
                @endforeach
            </ul>
        @else
            <span>—</span>
        @endif
    </td>
    <td data-label="Tenggat">
        @if ($deadline)
            <span>
                <time datetime="{{ $deadline->toDateString() }}" class="sg-browse-num">{{ $deadline->locale('id')->translatedFormat('j M Y') }}</time>
                <span class="sg-table__meta">{{ $daysLeft > 1 ? $daysLeft . ' hari lagi' : ($daysLeft === 1 ? 'besok' : ($daysLeft === 0 ? 'hari ini' : 'sudah lewat')) }}</span>
            </span>
        @else
            <span>Tanpa tenggat</span>
        @endif
    </td>
</tr>
