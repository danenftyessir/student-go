@extends('layouts.app')

@section('title', $jobPosting->title)

@section('content')
@php
    // phase 23: halaman rekaman lowongan. hanya kolom yang ada di job_postings; tidak ada angka atau status karangan.
    // form 'Referensi LinkedIn & SDG' tetap PATCH company.jobs.update-linkedin (linkedin_url, sdg_goals[]).
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $asArray = fn ($value) => is_array($value) ? $value : (is_string($value) ? (json_decode($value, true) ?: []) : []);
    $lines = fn ($text) => collect(preg_split('/\r\n|\r|\n/', (string) $text))->map(fn ($line) => trim(ltrim(trim($line), '-•*')))->filter()->values();

    $skills = collect($asArray($jobPosting->skills))->filter(fn ($skill) => is_string($skill) && $skill !== '')->values();
    $sdgs = collect($asArray($jobPosting->sdg_alignment))->map(fn ($id) => (int) $id)->filter(fn ($id) => $id >= 1 && $id <= 17)->unique()->sort()->values();
    $requiredSdgs = collect($asArray($jobPosting->required_sdgs))->map(fn ($id) => (int) $id)->filter(fn ($id) => $id >= 1 && $id <= 17)->unique()->sort()->values();

    $money = function ($amount) use ($jobPosting, $fmt) {
        return ($jobPosting->salary_currency === 'USD' ? 'US$ ' : 'Rp ') . $fmt($amount);
    };
    $salary = null;
    if ($jobPosting->salary_min || $jobPosting->salary_max) {
        $salary = $jobPosting->salary_min && $jobPosting->salary_max && (float) $jobPosting->salary_min !== (float) $jobPosting->salary_max
            ? $money($jobPosting->salary_min) . ' – ' . $money($jobPosting->salary_max)
            : $money($jobPosting->salary_min ?: $jobPosting->salary_max);
        $salary .= ['monthly' => ' per bulan', 'project' => ' per proyek'][$jobPosting->salary_period] ?? '';
    }

    // pelamar per tahap: nilai tersimpan -> label kosakata; nilai berlabel sama (pending/new) dijumlah
    $stages = collect(\App\Support\StatusVocabulary::values('job_application'))
        ->map(fn ($entry, $value) => ['label' => $entry[0], 'count' => $stageCounts[$value] ?? 0])
        ->groupBy('label')->map(fn ($rows) => $rows->sum('count'))->filter();
    $unknown = collect($stageCounts)->except(array_keys(\App\Support\StatusVocabulary::values('job_application')))->sum();
    $totalApplicants = array_sum($stageCounts);
    $applicantsUrl = route('company.applications.index', ['job_posting_id' => $jobPosting->id]);
@endphp

<div class="sg-container sg-co-job">
    <x-ui.page-header :title="$jobPosting->title"
                      :breadcrumbs="['Dashboard' => route('company.dashboard'), 'Lowongan' => route('company.jobs.index'), \Illuminate\Support\Str::limit($jobPosting->title, 48) => null]">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="pencil" :href="route('company.jobs.edit', $jobPosting->id)">Ubah lowongan</x-ui.button>
            @if ($totalApplicants > 0)
                <x-ui.button icon="users" :href="$applicantsUrl">Lihat pelamar ({{ $fmt($totalApplicants) }})</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- galat form referensi (modal tertutup setelah dimuat ulang) --}}
    <x-ui.form-errors title="Referensi belum tersimpan" />

    <p class="sg-co-job__meta">
        <x-ui.status domain="job_posting" :value="$jobPosting->status" />
        @if ($jobPosting->jobCategory)<span>{{ $jobPosting->jobCategory->name }}</span>@endif
        @if ($tanggal($jobPosting->created_at))<span>Dibuat {{ $tanggal($jobPosting->created_at) }}</span>@endif
    </p>

    <div class="sg-co-job__layout">
        <div class="sg-co-job__content">
            <section aria-labelledby="bagian-deskripsi">
                <h2 id="bagian-deskripsi">Deskripsi</h2>
                <p class="sg-co-job__text">{{ $jobPosting->description ?: '—' }}</p>
            </section>

            @foreach (['responsibilities' => 'Tanggung jawab', 'qualifications' => 'Kualifikasi', 'benefits' => 'Benefit'] as $field => $heading)
                @if ($lines($jobPosting->{$field})->isNotEmpty())
                    <section aria-labelledby="bagian-{{ $field }}">
                        <h2 id="bagian-{{ $field }}">{{ $heading }}</h2>
                        <ul class="sg-co-job__bullets">
                            @foreach ($lines($jobPosting->{$field}) as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endforeach

            <section aria-labelledby="bagian-keahlian">
                <h2 id="bagian-keahlian">Keahlian</h2>
                @if ($skills->isNotEmpty())
                    <ul class="sg-co-job__chips">
                        @foreach ($skills as $skill)<li class="sg-chip">{{ $skill }}</li>@endforeach
                    </ul>
                @else
                    <p class="sg-co-job__muted">Belum ada keahlian yang dicantumkan.</p>
                @endif
            </section>

            <section aria-labelledby="bagian-sdg">
                <h2 id="bagian-sdg">SDG Alignment</h2>
                @if ($sdgs->isNotEmpty())
                    <ul class="sg-co-job__chips">
                        @foreach ($sdgs as $id)<li class="sg-chip">SDG {{ $id }} · {{ sdg_label($id) }}</li>@endforeach
                    </ul>
                @else
                    <p class="sg-co-job__muted">Belum ada SDG yang dipilih.</p>
                @endif
            </section>

            <section aria-labelledby="bagian-dampak">
                <h2 id="bagian-dampak">Kriteria Dampak</h2>
                @if (filled($jobPosting->impact_metrics) || filled($jobPosting->success_criteria))
                    <x-ui.dl :items="['Metrik dampak' => $jobPosting->impact_metrics, 'Kriteria keberhasilan' => $jobPosting->success_criteria]" class="sg-co-job__impact" />
                @else
                    <p class="sg-co-job__muted">Belum ada kriteria dampak yang diisi.</p>
                @endif
            </section>
        </div>

        <aside class="sg-co-job__rail" aria-label="Fakta lowongan">
            <section aria-labelledby="fakta-lowongan">
                <h2 id="fakta-lowongan">Fakta lowongan</h2>
                <x-ui.dl :items="[
                    'Departemen' => $jobPosting->department,
                    'Tipe' => $jobTypeLabels[$jobPosting->job_type] ?? ($jobTypeLabels[ucfirst((string) $jobPosting->job_type)] ?? $jobPosting->job_type),
                    'Lokasi' => $jobPosting->location,
                    'Gaji atau honor' => $salary,
                    'Diterbitkan' => $tanggal($jobPosting->published_at),
                    'Berakhir' => $tanggal($jobPosting->expires_at),
                    'Dilihat' => $fmt($jobPosting->views_count) . ' kali',
                    'Pelamar' => $fmt($totalApplicants),
                ]" />
            </section>

            <section aria-labelledby="pelamar-tahap">
                <h2 id="pelamar-tahap">Pelamar per tahap</h2>
                @if ($totalApplicants > 0)
                    <ul class="sg-co-job__stages">
                        @foreach ($stages as $label => $count)
                            <li><span>{{ $label }}</span><b>{{ $fmt($count) }}</b></li>
                        @endforeach
                        @if ($unknown > 0)<li><span>Lainnya</span><b>{{ $fmt($unknown) }}</b></li>@endif
                    </ul>
                    @if (count($recentApplicants))
                        <h3 class="sg-co-job__subhead">Pelamar terbaru</h3>
                        <ul class="sg-list sg-co-job__recent">
                            @foreach (collect($recentApplicants)->take(5) as $applicant)
                                <li>
                                    <div class="sg-list__row">
                                        <x-ui.avatar :name="$applicant['name']" size="sm" decorative />
                                        <div class="sg-list__main">
                                            <p class="sg-list__title"><a href="{{ route('company.applications.show', $applicant['id']) }}" class="hover:underline">{{ $applicant['name'] }}</a></p>
                                            <p class="sg-list__meta">{{ $tanggal($applicant['created_at']) }}</p>
                                        </div>
                                        <div class="sg-list__end"><x-ui.status domain="job_application" :value="$applicant['status']" /></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <a href="{{ $applicantsUrl }}" class="sg-co-dash__more">Lihat semua pelamar</a>
                @else
                    <p class="sg-co-job__muted">Belum ada pelamar untuk lowongan ini.</p>
                @endif
            </section>

            <section aria-labelledby="referensi-lowongan">
                <h2 id="referensi-lowongan">Referensi LinkedIn &amp; SDG wajib</h2>
                <x-ui.dl>
                    <div>
                        <dt>Tautan LinkedIn</dt>
                        <dd>
                            @if ($jobPosting->linkedin_url)
                                <a href="{{ $jobPosting->linkedin_url }}" class="sg-co-job__link" rel="noopener noreferrer" target="_blank">{{ \Illuminate\Support\Str::limit($jobPosting->linkedin_url, 48) }}<span class="sr-only"> (membuka tab baru)</span></a>
                            @else
                                Belum ada
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>SDG wajib bagi pelamar</dt>
                        <dd>{{ $requiredSdgs->isNotEmpty() ? $requiredSdgs->map(fn ($id) => 'SDG ' . $id)->implode(', ') : 'Belum ada' }}</dd>
                    </div>
                </x-ui.dl>
                <x-ui.button variant="secondary" icon="pencil" data-sg-open="job-reference" class="sg-co-job__reference-btn">Ubah referensi</x-ui.button>
            </section>
        </aside>
    </div>

    <x-ui.modal id="job-reference" title="Referensi LinkedIn & SDG wajib">
        <form action="{{ route('company.jobs.update-linkedin', $jobPosting->id) }}" method="POST" id="form-referensi" class="sg-co-job__reference-form">
            @csrf
            @method('PATCH')
            <x-ui.field label="Tautan lowongan di LinkedIn (opsional)" for="linkedin_url" name="linkedin_url">
                <x-ui.input name="linkedin_url" id="linkedin_url" type="url" :value="old('linkedin_url', $jobPosting->linkedin_url)" placeholder="https://www.linkedin.com/jobs/view/…" />
            </x-ui.field>
            <x-ui.fieldset legend="SDG yang wajib dimiliki pelamar dari proyek KKN-nya" name="sdg_goals" help="Kosongkan bila tidak ada syarat SDG.">
                <div class="sg-co-job__sdg-checks">
                    @foreach (range(1, 17) as $id)
                        <x-ui.checkbox name="sdg_goals[]" :value="$id" :label="'SDG ' . $id . ' · ' . sdg_label($id)" :checked="$requiredSdgs->contains($id)" />
                    @endforeach
                </div>
            </x-ui.fieldset>
        </form>
        <x-slot:footer>
            <button type="button" class="sg-btn sg-btn--tertiary" data-sg-close>Batal</button>
            <button type="submit" form="form-referensi" class="sg-btn sg-btn--primary">Simpan referensi</button>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection
