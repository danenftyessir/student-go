@extends('layouts.app')

@section('title', 'Jaringan Anda')

{{-- phase 13: jaringan sebagai direktori orang. tiga tab dalam halaman (koneksi, permintaan, saran) dari data yang
     sudah dikirim controller ($friends, $pendingRequests, $suggestions, $stats); tanpa foto hero dan tanpa ubin statistik.
     nama dan inisial dari Student::display_name (D-03); kontak tidak tampil di daftar (D-04).
     pencarian koneksi = GET ?search= (difilter controller), tanpa JS. --}}

@php
    $indexUrl = route('student.friends.index');
    $isSearching = filled(request('search'));
    $friendsSorted = $friends->filter()->sortBy(fn ($f) => mb_strtolower($f->display_name))->values();
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    $metaOf = fn ($s) => collect([$s->major, $s->university->name ?? null])->filter()->implode(' · ');
    $photoOf = fn ($s) => $s->profile_photo_path ? $s->profile_photo_url : null;

    // tab awal: permintaan masuk lebih dulu bila ada (sebelumnya tampil paling atas), koneksi bila sedang mencari
    $selected = (! $isSearching && $pendingRequests->count() > 0) ? 'permintaan' : 'koneksi';
    $tabs = [
        'koneksi' => 'Koneksi (' . $fmt($stats['total_friends']) . ')',
        'permintaan' => 'Permintaan (' . $fmt($stats['pending_requests']) . ')',
        'saran' => 'Saran',
    ];
@endphp

@section('content')
<div class="sg-container pb-16">
    <x-ui.page-header title="Jaringan Anda" description="Koneksi, permintaan masuk dan saran mahasiswa KKN dari universitas atau jurusan yang sama.">
        <x-slot:actions>
            <x-ui.button icon="user-plus" :href="route('student.friends.search')">Tambah Koneksi</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.tablist :tabs="$tabs" :selected="$selected" label="Bagian jaringan" prefix="jaringan">
        {{-- koneksi --}}
        <x-ui.tab-panel id="koneksi" prefix="jaringan" :active="$selected === 'koneksi'" class="pt-5">
            @if ($stats['total_friends'] > 0)
                <form method="GET" action="{{ $indexUrl }}" role="search" class="flex flex-wrap items-end gap-2">
                    <x-ui.search name="search" id="friend-search" label="Cari koneksi" placeholder="Cari nama atau universitas" class="min-w-0 flex-1 basis-64" />
                    <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
                    @if ($isSearching)
                        <x-ui.button variant="tertiary" :href="$indexUrl">Atur ulang</x-ui.button>
                    @endif
                </form>

                <p class="mt-4 mb-3 text-sm text-gray-700 tabular-nums" aria-live="polite">
                    @if ($isSearching)
                        <b class="font-semibold text-gray-900">{{ $fmt($friendsSorted->count()) }}</b> dari {{ $fmt($stats['total_friends']) }} koneksi
                    @else
                        <b class="font-semibold text-gray-900">{{ $fmt($friendsSorted->count()) }}</b> koneksi
                    @endif
                </p>

                @if ($friendsSorted->count() > 0)
                    <x-ui.list label="Daftar koneksi" id="friends-list">
                        @foreach ($friendsSorted as $friend)
                            <x-ui.list-item :title="$friend->display_name" :href="route('profile.public', $friend->user->username)" :meta="$metaOf($friend)"
                                            data-student-id="{{ $friend->id }}">
                                <x-slot:leading>
                                    <x-ui.avatar :src="$photoOf($friend)" :name="$friend->display_name" />
                                </x-slot:leading>
                            </x-ui.list-item>
                        @endforeach
                    </x-ui.list>
                @else
                    <x-ui.empty-state illustration="no-search-results" title="Tidak ada koneksi yang cocok"
                                      description="Coba nama atau universitas lain.">
                        <x-slot:action>
                            <x-ui.button variant="secondary" :href="$indexUrl">Atur ulang</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @endif
            @else
                {{-- keadaan kosong: ikon orang dan ajakan (dipertahankan dari halaman lama) --}}
                <div class="sg-empty">
                    <span class="mb-2 inline-flex size-20 items-center justify-center rounded-full bg-gray-100 text-gray-500" aria-hidden="true">
                        <x-icon name="users" size="lg" class="m-0! size-10!" />
                    </span>
                    <h2>Belum ada koneksi</h2>
                    <p>Mulai bangun jaringan Anda dengan terhubung ke mahasiswa KKN lainnya.</p>
                    {{-- tanpa ikon: .sg-empty svg membesarkan semua svg di dalamnya --}}
                    <x-ui.button variant="secondary" :href="route('student.friends.search')">Cari mahasiswa</x-ui.button>
                </div>
            @endif
        </x-ui.tab-panel>

        {{-- permintaan masuk --}}
        <x-ui.tab-panel id="permintaan" prefix="jaringan" :active="$selected === 'permintaan'" class="pt-5">
            @if ($pendingRequests->count() > 0)
                <x-ui.list label="Permintaan koneksi masuk">
                    @foreach ($pendingRequests as $request)
                        @php $requester = $request->requester; @endphp
                        <x-ui.list-item :title="$requester->display_name" :href="route('profile.public', $requester->user->username)" :meta="$metaOf($requester)"
                                        data-friendship-id="{{ $request->id }}">
                            <x-slot:leading>
                                <x-ui.avatar :src="$photoOf($requester)" :name="$requester->display_name" />
                            </x-slot:leading>
                            @if (filled($request->message))
                                <p class="mt-2 mb-0 max-w-prose text-sm text-gray-800 wrap-break-word">“{{ $request->message }}”</p>
                            @endif
                            <p class="mt-1 mb-0 text-xs text-gray-600">
                                <time datetime="{{ $request->created_at->toIso8601String() }}">{{ $request->created_at->locale('id')->diffForHumans() }}</time>
                            </p>
                            {{-- aksi di bawah teks agar tidak menyempitkan nama di layar kecil --}}
                            <div class="mt-3 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('student.friends.accept', $request->id) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" icon="check" aria-label="Terima permintaan {{ $requester->display_name }}">Terima</x-ui.button>
                                </form>
                                <form method="POST" action="{{ route('student.friends.reject', $request->id) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="tertiary" aria-label="Tolak permintaan {{ $requester->display_name }}">Tolak</x-ui.button>
                                </form>
                            </div>
                        </x-ui.list-item>
                    @endforeach
                </x-ui.list>
            @else
                <x-ui.empty-state title="Tidak ada permintaan masuk"
                                  description="Permintaan koneksi dari mahasiswa lain akan muncul di sini." />
            @endif

            @if ($stats['sent_requests'] > 0)
                <p class="mt-4 mb-0 text-sm text-gray-700 tabular-nums">
                    <x-icon name="hourglass" size="sm" class="text-gray-500" />
                    {{ $fmt($stats['sent_requests']) }} permintaan yang Anda kirim masih menunggu jawaban.
                </p>
            @endif
        </x-ui.tab-panel>

        {{-- saran: mahasiswa dari universitas atau jurusan yang sama, belum terhubung dan tanpa permintaan tertunda --}}
        <x-ui.tab-panel id="saran" prefix="jaringan" :active="$selected === 'saran'" class="pt-5">
            @if ($suggestions->count() > 0)
                <p class="mt-0 mb-3 text-sm text-gray-600">Mahasiswa dari universitas atau jurusan yang sama dengan Anda.</p>
                <x-ui.list label="Saran koneksi">
                    @foreach ($suggestions as $suggestion)
                        <x-ui.list-item :title="$suggestion->display_name" :href="route('student.friends.profile', $suggestion->id)" :meta="$metaOf($suggestion)"
                                        data-student-id="{{ $suggestion->id }}">
                            <x-slot:leading>
                                <x-ui.avatar :src="$photoOf($suggestion)" :name="$suggestion->display_name" />
                            </x-slot:leading>
                            <x-slot:end>
                                <form method="POST" action="{{ route('student.friends.send-request', $suggestion->id) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" icon="user-plus" aria-label="Hubungkan dengan {{ $suggestion->display_name }}">Hubungkan</x-ui.button>
                                </form>
                            </x-slot:end>
                        </x-ui.list-item>
                    @endforeach
                </x-ui.list>
                <p class="mt-4 mb-0 text-sm"><a href="{{ route('student.friends.search') }}" class="text-brand-700 underline">Cari mahasiswa lain</a></p>
            @else
                <x-ui.empty-state title="Belum ada saran"
                                  description="Belum ada mahasiswa lain dari universitas atau jurusan yang sama yang belum terhubung dengan Anda.">
                    <x-slot:action>
                        <x-ui.button variant="secondary" :href="route('student.friends.search')">Cari mahasiswa</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @endif
        </x-ui.tab-panel>
    </x-ui.tablist>
</div>
@endsection
