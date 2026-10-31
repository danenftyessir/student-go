{{-- kartu galeri (phase 8, D-14): gambar pertama masalah hanya bila ada record gambar; bila gagal dimuat area media hilang
     (x-ui.card), tanpa tile pengganti. isi yang sama dengan baris daftar. --}}
@php
    $level = $difficulties[$problem->difficulty_level] ?? null;
    $sdgs = array_values(array_filter(array_map('intval', (array) $problem->sdg_categories)));
    $deadline = $problem->application_deadline;
    $applied = $applicationStatuses[$problem->id] ?? null;
    $image = optional($problem->images->first())->image_path;
@endphp
<x-ui.card :title="$problem->title" :href="route('student.browse-problems.show', $problem->id)"
           :image="$image ? supabase_url($image) : null" image-alt="" class="sg-browse-card" data-problem-card="{{ $problem->id }}">
    <p class="sg-browse-card__by">{{ optional($problem->institution)->name }}</p>
    @if ($applied || $problem->is_urgent || $problem->is_featured)
        <p class="sg-browse-row__flags">
            @if ($applied)<x-ui.status domain="application" :value="$applied" />@endif
            @if ($problem->is_urgent)<span class="sg-browse-flag"><x-icon name="clock" size="sm" />Mendesak</span>@endif
            @if ($problem->is_featured)<span class="sg-browse-flag"><x-icon name="award" size="sm" />Unggulan</span>@endif
        </p>
    @endif
    <dl class="sg-browse-card__facts">
        <div><dt>Lokasi</dt><dd>{{ collect([optional($problem->regency)->name, optional($problem->province)->name])->filter()->implode(', ') ?: '—' }}</dd></div>
        <div><dt>Kuota</dt><dd>{{ $problem->required_students }} mahasiswa</dd></div>
        <div><dt>Tenggat</dt><dd>@if ($deadline)<time datetime="{{ $deadline->toDateString() }}">{{ $deadline->locale('id')->translatedFormat('j M Y') }}</time>@else — @endif</dd></div>
        @if ($level)
            <div><dt>Kesulitan</dt><dd><span class="sg-meter" data-level="{{ $level[0] }}"><span class="sg-meter__bars" aria-hidden="true"><i></i><i></i><i></i></span>{{ $level[1] }}</span></dd></div>
        @endif
    </dl>
    @if (count($sdgs))
        <ul class="sg-browse-row__sdgs" aria-label="Tujuan SDG">
            @foreach ($sdgs as $sdg)
                <li><x-ui.chip>SDG {{ $sdg }} · {{ \App\Http\Controllers\HomeController::sdgName($sdg) }}</x-ui.chip></li>
            @endforeach
        </ul>
    @endif
</x-ui.card>
