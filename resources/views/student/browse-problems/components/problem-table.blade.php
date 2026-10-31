{{-- daftar masalah (tampilan default, phase 8): satu baris per masalah; kolom Masalah · Instansi · Lokasi · Kuota · Tenggat · Kesulitan.
     nama instansi utuh dan membungkus; di bawah 640px baris ditumpuk (x-ui.table). urut tenggat memakai parameter sort=deadline yang sudah ada. --}}
@php
    $deadlineSorted = $currentSort === 'deadline';
    $sortQuery = $query;
    if ($deadlineSorted) {
        unset($sortQuery['sort']);
    } else {
        $sortQuery['sort'] = 'deadline';
    }
    $sortUrl = $indexUrl . ($sortQuery ? '?' . http_build_query($sortQuery) : '');
@endphp
<x-ui.table caption="Masalah KKN yang dibuka, halaman {{ $problems->currentPage() }}" class="sg-browse-table">
    <x-slot:head>
        <th scope="col">Masalah</th>
        <th scope="col">Instansi</th>
        <th scope="col">Lokasi</th>
        <th scope="col">Kuota</th>
        <th scope="col" @if ($deadlineSorted) aria-sort="ascending" @endif>
            <a href="{{ $sortUrl }}" class="sg-browse-table__sort">
                Tenggat<x-icon :name="$deadlineSorted ? 'chevron-up' : 'chevron-down'" size="sm" />
                <span class="sr-only">{{ $deadlineSorted ? '(diurutkan dari yang terdekat; klik untuk urutan terbaru)' : '(urutkan dari yang terdekat)' }}</span>
            </a>
        </th>
        <th scope="col">Kesulitan</th>
    </x-slot:head>
    @foreach ($problems as $problem)
        @include('student.browse-problems.components.problem-card-list', ['problem' => $problem])
    @endforeach
</x-ui.table>
