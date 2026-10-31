@extends('layouts.app')

@section('title', 'Notifikasi')

{{-- phase 16: satu template kotak masuk untuk mahasiswa, instansi dan perusahaan. data dan otorisasi dari
     NotificationController (hanya notifikasi milik pengguna). parameter filter tetap: status (unread/read), type, page.
     ikon menurut tipe (App\Support\NotificationTypes), emoji di judul dibuang saat render (data tidak diubah).
     membuka halaman tidak menandai notifikasi sebagai dibaca. --}}

@use('App\Support\NotificationTypes')

@php
    $indexUrl = route('notifications.index');
    $statusOptions = ['unread' => 'Belum dibaca', 'read' => 'Sudah dibaca'];
    $typeOptions = NotificationTypes::options($types);
    $currentType = request('type');
    if (filled($currentType) && ! array_key_exists($currentType, $typeOptions)) {
        $typeOptions[$currentType] = NotificationTypes::label($currentType);
    }
    $activeCount = collect(['status', 'type'])->filter(fn ($k) => filled(request($k)))->count();
    $isFiltered = $activeCount > 0;

    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
    // hanya angka bukan nol (guardrail 2)
    $summary = [
        ['value' => $stats['total'] ?: null, 'label' => 'notifikasi'],
        ['value' => $stats['unread'] ?: null, 'label' => 'belum dibaca'],
    ];

    // kelompok per tanggal (halaman ini saja)
    $groups = $notifications->getCollection()->groupBy(fn ($n) => $n->created_at->toDateString());
    $groupLabel = function (string $date) {
        $day = \Carbon\Carbon::parse($date);
        if ($day->isToday()) {
            return 'Hari ini';
        }
        if ($day->isYesterday()) {
            return 'Kemarin';
        }

        return format_date_indonesian($day, 'long');
    };

    $role = auth()->user()->user_type;
    $emptyText = [
        'student' => 'Notifikasi muncul saat aplikasi Anda diputuskan, proyek dimulai, atau ada permintaan pertemanan.',
        'institution' => 'Notifikasi muncul saat mahasiswa mengirim aplikasi atau laporan proyek.',
    ][$role] ?? 'Notifikasi untuk akun Anda akan muncul di halaman ini.';
    $emptyAction = [
        'student' => [route('student.browse-problems.index'), 'Telusuri masalah'],
        'institution' => [route('institution.applications.index'), 'Lihat aplikasi'],
        'company' => [route('company.dashboard'), 'Ke dashboard'],
    ][$role] ?? null;
@endphp

@section('content')
<div class="sg-container sg-inbox">
    <x-ui.page-header title="Notifikasi" description="Pembaruan tentang aplikasi, proyek dan akun Anda. Notifikasi baru tetap belum dibaca sampai Anda membukanya.">
        @if ($stats['unread'] > 0 || $stats['read'] > 0)
            <x-slot:actions>
                @if ($stats['unread'] > 0)
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" icon="check">Tandai semua dibaca</x-ui.button>
                    </form>
                @endif
                @if ($stats['read'] > 0)
                    <form action="{{ route('notifications.destroy-read') }}" method="POST"
                          data-sg-confirm="Semua notifikasi yang sudah dibaca akan dihapus. Tindakan ini tidak dapat dibatalkan."
                          data-sg-confirm-title="Hapus notifikasi yang sudah dibaca?" data-sg-confirm-label="Hapus">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" icon="trash-2">Hapus yang sudah dibaca</x-ui.button>
                    </form>
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($stats['total'] > 0)
        <x-ui.filter-bar :action="$indexUrl" :search="false" :reset-href="$isFiltered ? $indexUrl : null" :active="$activeCount" id="inbox-filter" class="sg-inbox__filters">
            <x-ui.field label="Status" for="filter-status">
                <x-ui.select name="status" id="filter-status" :options="$statusOptions" :selected="request('status')" placeholder="Semua status" />
            </x-ui.field>
            <x-ui.field label="Tipe notifikasi" for="filter-type">
                <x-ui.select name="type" id="filter-type" :options="$typeOptions" :selected="$currentType" placeholder="Semua tipe" />
            </x-ui.field>
        </x-ui.filter-bar>

        <div class="sg-inbox__count" aria-live="polite">
            @if ($isFiltered)
                <p><b>{{ $fmt($notifications->total()) }}</b> dari {{ $fmt($stats['total']) }} notifikasi cocok dengan filter</p>
            @else
                <x-ui.summary :items="$summary" />
            @endif
        </div>
    @endif

    @if ($notifications->count() > 0)
        @foreach ($groups as $date => $items)
            <section class="sg-inbox__group" aria-labelledby="inbox-day-{{ $date }}">
                <h2 id="inbox-day-{{ $date }}" class="sg-inbox__day">{{ $groupLabel($date) }}</h2>
                <ul class="sg-list sg-inbox__list">
                    @foreach ($items as $notification)
                        @php $title = NotificationTypes::cleanTitle($notification->title) ?: NotificationTypes::label($notification->type); @endphp
                        <li class="sg-inbox__item" @if (! $notification->is_read) data-unread @endif>
                            <div class="sg-inbox__row">
                                <span class="sg-inbox__icon"><x-icon :name="NotificationTypes::icon($notification->type)" /></span>
                                <div class="sg-inbox__main">
                                    <p class="sg-inbox__title">{{ $title }}</p>
                                    @if (filled($notification->message))
                                        <p class="sg-inbox__msg">{{ $notification->message }}</p>
                                    @endif
                                    <p class="sg-inbox__meta">
                                        <span>{{ NotificationTypes::label($notification->type) }}</span>
                                        <span aria-hidden="true">·</span>
                                        <time datetime="{{ $notification->created_at->toIso8601String() }}" title="{{ format_date_indonesian($notification->created_at, 'long') }}, {{ $notification->created_at->format('H:i') }}">{{ time_ago($notification->created_at) }}</time>
                                        <span aria-hidden="true">·</span>
                                        @if ($notification->is_read)
                                            <span>Sudah dibaca</span>
                                        @else
                                            <span class="sg-inbox__unread"><span class="sg-inbox__dot" aria-hidden="true"></span>Belum dibaca</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="sg-inbox__actions">
                                    {{-- endpoint sama: POST /notifications/{id}/read menandai dibaca lalu membuka action_url bila ada --}}
                                    @if ($notification->action_url)
                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="sg-btn sg-btn--secondary" aria-label="Buka: {{ $title }}">Buka</button>
                                        </form>
                                    @elseif (! $notification->is_read)
                                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            <x-ui.icon-button type="submit" icon="check" :label="'Tandai dibaca: ' . $title" bordered />
                                        </form>
                                    @endif
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
                                          data-sg-confirm="Notifikasi “{{ $title }}” akan dihapus." data-sg-confirm-title="Hapus notifikasi?" data-sg-confirm-label="Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.icon-button type="submit" icon="trash-2" :label="'Hapus notifikasi: ' . $title" bordered />
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach

        <x-ui.pagination :paginator="$notifications" label="Halaman notifikasi" class="sg-inbox__pages" />
    @elseif ($isFiltered)
        <x-ui.empty-state illustration="no-search-results" title="Tidak ada notifikasi yang cocok"
            description="Ubah status atau tipe notifikasi, atau atur ulang filter.">
            <x-slot:action>
                <x-ui.button :href="$indexUrl" variant="secondary">Atur ulang filter</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state illustration="no-notifications" title="Belum ada notifikasi" :description="$emptyText">
            @if ($emptyAction)
                <x-slot:action>
                    <x-ui.button :href="$emptyAction[0]" variant="secondary">{{ $emptyAction[1] }}</x-ui.button>
                </x-slot:action>
            @endif
        </x-ui.empty-state>
    @endif
</div>
@endsection
