{{-- phase 12: portofolio mahasiswa, dipakai /student/profile (owner = true) dan /profile/{username} (owner = false).
     identitas dari Student::display_name/initials (D-03); kontak hanya bila $canViewContact (D-04, diputuskan di controller).
     foto: hanya foto unggahan mahasiswa di slot avatar, tanpa foto lain (D-19); tanpa foto -> inisial.
     variabel: $student, $projects (proyek berstatus completed), $owner, $canViewContact, $summary, $shareUrl --}}
@php
    $name = $student->display_name;
    $university = $student->university->name ?? null;
    $stories = array_values(array_filter((array) ($student->stories ?? []), fn ($s) => filled(trim((string) $s))));
    $ownSkills = array_values(array_filter((array) ($student->skills ?? []), fn ($s) => filled(trim((string) $s))));
    $interests = array_values(array_filter((array) ($student->interests ?? []), fn ($s) => filled(trim((string) $s))));
    // keterampilan yang diminta masalah dari proyek selesai (PortfolioService), dipisah dari isian mahasiswa sendiri
    $projectSkills = array_values(array_diff(array_filter((array) ($skills ?? []), 'filled'), $ownSkills));
    $bulanTahun = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('M Y') : null;
    $arr = fn ($v) => is_array($v) ? $v : (json_decode((string) $v, true) ?: []);
    $semester = $student->semester ? 'Semester ' . $student->semester : null;
@endphp

<div class="sg-container pb-16">
    {{-- kepala identitas: tanpa foto latar, tanpa ubin statistik (J6) --}}
    <header class="flex flex-col gap-5 border-b border-gray-200 pt-8 pb-6 sm:flex-row sm:items-start">
        <x-ui.avatar :src="$student->profile_photo_path ? $student->profile_photo_url : null" :name="$name" size="xl"
                     alt="Foto profil {{ $name }}" />
        <div class="min-w-0 flex-1">
            <p class="m-0 text-sm text-gray-600">{{ $owner ? 'Profil saya' : 'Portofolio mahasiswa' }}</p>
            <h1 class="mt-1 mb-0 text-2xl leading-8 font-semibold text-gray-900 wrap-break-word sm:text-3xl sm:leading-9">{{ $name }}</h1>
            <p class="mt-2 mb-0 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-700">
                @if (filled($student->major))
                    <span class="inline-flex items-center gap-1.5"><x-icon name="book-open" size="sm" class="text-gray-500" />{{ $student->major }}</span>
                @endif
                @if ($university)
                    <span class="inline-flex min-w-0 items-center gap-1.5"><x-icon name="graduation-cap" size="sm" class="flex-none text-gray-500" /><span class="wrap-break-word">{{ $university }}</span></span>
                @endif
                @if ($semester)
                    <span>{{ $semester }}</span>
                @endif
            </p>
            @if (filled($student->bio))
                <p class="mt-4 mb-0 max-w-prose text-base leading-7 text-gray-800 wrap-break-word">{{ $student->bio }}</p>
            @elseif ($owner)
                <p class="mt-4 mb-0 max-w-prose text-sm text-gray-600">Belum ada ringkasan diri. <a href="{{ route('student.profile.edit') }}#bagian-portofolio" class="text-brand-700 underline">Tulis bio singkat</a></p>
            @endif
        </div>
        @if ($owner)
            <div class="flex flex-wrap gap-2 sm:flex-none sm:justify-end">
                <x-ui.button :href="route('student.profile.edit')" icon="pencil">Edit profil</x-ui.button>
                <x-ui.button variant="secondary" icon="copy" data-sg-copy="{{ $shareUrl }}">Salin tautan portofolio</x-ui.button>
            </div>
        @endif
    </header>

    <x-ui.summary label="Ringkasan portofolio" class="mt-4" :items="$summary" />

    <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_18rem] lg:gap-12">
        <div class="min-w-0 space-y-10">
            {{-- cerita: kolom bacaan (lapisan catatan lapangan) --}}
            <section aria-labelledby="judul-cerita">
                <h2 id="judul-cerita" class="m-0 text-lg leading-7 font-semibold text-gray-900">Cerita pengalaman</h2>
                @if (count($stories))
                    <div class="mt-4 divide-y divide-gray-200 border-y border-gray-200">
                        @foreach ($stories as $story)
                            <article class="py-5" aria-label="Cerita {{ $loop->iteration }} dari {{ count($stories) }}">
                                @if (count($stories) > 1)
                                    <p class="m-0 mb-2 text-xs font-semibold tracking-wide text-gray-600 tabular-nums">Cerita {{ $loop->iteration }} dari {{ count($stories) }}</p>
                                @endif
                                <div class="sg-prose wrap-break-word">{!! nl2br(e(trim($story))) !!}</div>
                            </article>
                        @endforeach
                    </div>
                @elseif ($owner)
                    <x-ui.empty-state class="mt-4" title="Belum ada cerita" :heading-level="3"
                                      description="Tulis pengalaman KKN Anda: apa yang dikerjakan, bersama siapa, dan apa yang dipelajari. Cerita tampil di profil publik.">
                        <x-ui.button variant="secondary" :href="route('student.profile.edit') . '#bagian-portofolio'">Tulis cerita</x-ui.button>
                    </x-ui.empty-state>
                @else
                    <p class="mt-3 mb-0 text-sm text-gray-600">{{ $name }} belum menulis cerita pengalaman.</p>
                @endif
            </section>

            {{-- proyek selesai: daftar bergaris, instansi dan tahun dari data proyek --}}
            <section aria-labelledby="judul-proyek">
                <h2 id="judul-proyek" class="m-0 text-lg leading-7 font-semibold text-gray-900">Proyek selesai</h2>
                <p class="mt-1 mb-0 text-sm text-gray-600">
                    @if ($owner)
                        Proyek KKN berstatus Selesai. Atur proyek mana yang tampil di profil publik.
                    @else
                        Proyek KKN berstatus Selesai yang ditampilkan oleh pemilik profil.
                    @endif
                </p>

                @if ($projects->isEmpty())
                    @if ($owner)
                        <x-ui.empty-state class="mt-4" title="Belum ada proyek selesai" :heading-level="3"
                                          description="Proyek muncul di sini setelah statusnya Selesai. Mulai dengan melamar masalah yang sesuai minat Anda.">
                            <x-ui.button variant="secondary" :href="route('student.browse-problems.index')">Telusuri masalah</x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <p class="mt-3 mb-0 text-sm text-gray-600">Belum ada proyek selesai yang ditampilkan.</p>
                    @endif
                @else
                    <ul class="mt-4 divide-y divide-gray-200 border-y border-gray-200" aria-label="Proyek selesai">
                        @foreach ($projects as $project)
                            @php
                                $problem = $project->problem;
                                $title = $problem->title ?? $project->title;
                                $lokasi = collect([$problem->regency->name ?? null, $problem->province->name ?? null])->filter()->implode(', ');
                                $selesai = $bulanTahun($project->actual_end_date ?? $project->completed_at);
                                $sdgs = $problem ? $arr($problem->sdg_categories) : [];
                                $review = $project->institutionReview;
                            @endphp
                            <li class="py-5" @if ($owner) x-data="sgPortfolioVisibility({{ $project->id }}, @js((bool) $project->is_portfolio_visible))" @endif>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="m-0 text-base leading-6 font-semibold text-gray-900 wrap-break-word">{{ $title }}</h3>
                                        <p class="mt-1 mb-0 flex flex-wrap gap-x-3 gap-y-1 text-sm text-gray-700">
                                            @if ($problem?->institution)
                                                <span class="inline-flex min-w-0 items-center gap-1.5"><x-icon name="building-2" size="sm" class="flex-none text-gray-500" /><span class="wrap-break-word">{{ $problem->institution->name }}</span></span>
                                            @endif
                                            @if ($lokasi !== '')
                                                <span class="inline-flex items-center gap-1.5"><x-icon name="map-pin" size="sm" class="flex-none text-gray-500" />{{ $lokasi }}</span>
                                            @endif
                                            @if ($selesai)
                                                <span class="inline-flex items-center gap-1.5 tabular-nums"><x-icon name="calendar" size="sm" class="flex-none text-gray-500" />Selesai {{ $selesai }}</span>
                                            @endif
                                            @if (filled($project->role_in_team))
                                                <span class="inline-flex items-center gap-1.5"><x-icon name="user" size="sm" class="flex-none text-gray-500" />{{ $project->role_in_team }}</span>
                                            @endif
                                        </p>
                                        @if (filled($problem?->description))
                                            <p class="mt-2 mb-0 max-w-prose text-sm leading-6 text-gray-700 wrap-break-word">{{ \Illuminate\Support\Str::limit($problem->description, 220) }}</p>
                                        @endif
                                        @if (count($sdgs))
                                            <p class="mt-2 mb-0 flex flex-wrap gap-1.5" aria-label="Kategori SDG">
                                                @foreach ($sdgs as $sdg)
                                                    <x-ui.chip>SDG {{ $sdg }}</x-ui.chip>
                                                @endforeach
                                            </p>
                                        @endif

                                        {{-- bukti dari instansi: ulasan dan sertifikat (hanya bila benar-benar ada) --}}
                                        @if ($review || $project->certificate_path)
                                            <div class="mt-3 space-y-2">
                                                @if ($review)
                                                    <p class="m-0 inline-flex items-center gap-1.5 text-sm text-gray-800">
                                                        <x-icon name="badge-check" size="sm" class="text-brand-700" />Diulas instansi
                                                        @if ($review->rating)
                                                            <span class="tabular-nums">· nilai {{ number_format((float) $review->rating, 1, ',', '.') }} dari 5</span>
                                                        @endif
                                                    </p>
                                                    @if (filled($review->comment))
                                                        <blockquote class="m-0 max-w-prose border-l-2 border-gray-300 pl-3 text-sm leading-6 text-gray-700 wrap-break-word">{{ $review->comment }}</blockquote>
                                                    @endif
                                                @endif
                                                @if ($project->certificate_path)
                                                    <p class="m-0 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-800">
                                                        <span class="inline-flex items-center gap-1.5"><x-icon name="award" size="sm" class="text-brand-700" />Sertifikat</span>
                                                        @if ($project->certificate_number)
                                                            <span class="text-gray-600 tabular-nums">No. {{ $project->certificate_number }}</span>
                                                        @endif
                                                        <a href="{{ document_url($project->certificate_path) }}" target="_blank" download
                                                           class="inline-flex items-center gap-1 text-brand-700 underline">
                                                            <x-icon name="download" size="sm" />Unduh sertifikat<span class="sr-only"> {{ $title }}</span>
                                                        </a>
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if ($owner)
                                        {{-- tampil/sembunyi di profil publik: endpoint toggle-visibility yang sama --}}
                                        <label class="sg-check text-sm sm:flex-none" for="tampil-{{ $project->id }}">
                                            <input type="checkbox" id="tampil-{{ $project->id }}" role="switch" @checked($project->is_portfolio_visible)
                                                   :checked="visible" @change.prevent="toggle($event)" :disabled="busy" :aria-busy="busy.toString()">
                                            <span>Tampil di profil publik<span class="sr-only">: {{ $title }}</span></span>
                                        </label>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <aside class="min-w-0 space-y-8" aria-label="Keterampilan dan kontak">
            <section aria-labelledby="judul-keterampilan">
                <h2 id="judul-keterampilan" class="m-0 text-base leading-6 font-semibold text-gray-900">Keterampilan</h2>
                @if (count($ownSkills))
                    <ul class="mt-3 flex flex-wrap gap-1.5" aria-label="Keterampilan">
                        @foreach ($ownSkills as $skill)
                            <li><x-ui.chip class="text-sm! leading-5!">{{ $skill }}</x-ui.chip></li>
                        @endforeach
                    </ul>
                @elseif ($owner)
                    <p class="mt-2 mb-0 text-sm text-gray-600">Belum ada keterampilan. <a href="{{ route('student.profile.edit') }}#bagian-portofolio" class="text-brand-700 underline">Tambahkan</a></p>
                @else
                    <p class="mt-2 mb-0 text-sm text-gray-600">Belum diisi.</p>
                @endif

                @if (count($projectSkills))
                    <h3 class="mt-4 mb-0 text-sm font-semibold text-gray-800">Dipakai dalam proyek selesai</h3>
                    <ul class="mt-2 flex flex-wrap gap-1.5" aria-label="Keterampilan dari proyek selesai">
                        @foreach ($projectSkills as $skill)
                            <li><x-ui.chip>{{ $skill }}</x-ui.chip></li>
                        @endforeach
                    </ul>
                @endif

                @if (count($interests))
                    <h3 class="mt-4 mb-0 text-sm font-semibold text-gray-800">Minat</h3>
                    <ul class="mt-2 flex flex-wrap gap-1.5" aria-label="Minat">
                        @foreach ($interests as $interest)
                            <li><x-ui.chip>{{ $interest }}</x-ui.chip></li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- kontak privat (D-04): nilai hanya dikirim bila $canViewContact --}}
            <section aria-labelledby="judul-kontak">
                <h2 id="judul-kontak" class="m-0 text-base leading-6 font-semibold text-gray-900">Kontak</h2>
                @if ($canViewContact)
                    <x-ui.dl class="mt-3">
                        <div>
                            <dt>Email</dt>
                            <dd class="break-all">{{ $student->user->email }}</dd>
                        </div>
                        @if (filled($student->whatsapp))
                            <div>
                                <dt>WhatsApp</dt>
                                <dd class="tabular-nums">{{ $student->whatsapp }}</dd>
                            </div>
                        @endif
                    </x-ui.dl>
                    <p class="mt-3 mb-0 flex gap-1.5 text-xs leading-4 text-gray-600"><x-icon name="lock" size="sm" class="flex-none" />Hanya terlihat oleh {{ $owner || auth()->id() === $student->user_id ? 'Anda' : 'pemilik profil' }} dan koneksi yang sudah diterima.</p>
                @else
                    <p class="mt-2 mb-0 flex gap-1.5 text-sm text-gray-600"><x-icon name="lock" size="sm" class="mt-0.5 flex-none" />Email dan WhatsApp hanya terlihat oleh koneksi yang sudah diterima.</p>
                @endif
            </section>

            @if ($owner)
                @php
                    $shareText = 'Lihat portofolio KKN saya di Student Go: ' . $name;
                    $shareLinks = [
                        'WhatsApp' => 'https://wa.me/?text=' . rawurlencode($shareText . "\n" . $shareUrl),
                        'Email' => 'mailto:?subject=' . rawurlencode('Portofolio ' . $name . ' di Student Go') . '&body=' . rawurlencode($shareText . "\n\n" . $shareUrl),
                        'LinkedIn' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($shareUrl),
                        'X' => 'https://twitter.com/intent/tweet?text=' . rawurlencode($shareText) . '&url=' . rawurlencode($shareUrl),
                    ];
                @endphp
                <section aria-labelledby="judul-tautan">
                    <h2 id="judul-tautan" class="m-0 text-base leading-6 font-semibold text-gray-900">Tautan portofolio</h2>
                    <p class="mt-2 mb-0 text-sm break-all text-gray-800"><a href="{{ $shareUrl }}" class="text-brand-700 underline" target="_blank" rel="noopener">{{ $shareUrl }}<span class="sr-only"> (buka profil publik di tab baru)</span></a></p>
                    <p class="mt-3 mb-1 text-xs text-gray-600">Bagikan lewat</p>
                    <ul class="m-0 flex flex-wrap gap-x-4 gap-y-1 p-0 text-sm" aria-label="Bagikan portofolio">
                        @foreach ($shareLinks as $label => $href)
                            <li class="list-none"><a href="{{ $href }}" @unless ($label === 'Email') target="_blank" rel="noopener" @endunless class="text-brand-700 underline">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</div>
