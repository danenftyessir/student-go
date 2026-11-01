@extends('layouts.app')

@section('title', 'Ulasan Mahasiswa')

@section('content')
@php
    // phase 19: daftar ulasan bergaris pemisah (mahasiswa · proyek · rating · komentar · tanggal).
    // filter lama tetap: search, rating, sort. ringkasan hanya dari statistik controller (angka nyata).
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $filtered = request()->filled('search') || request()->filled('rating');
    $activeFilters = (int) request()->filled('rating') + (int) (request()->filled('sort') && request('sort') !== 'latest');
    $ratingOptions = [5 => '5 · Istimewa', 4 => '4 · Sangat baik', 3 => '3 · Baik', 2 => '2 · Cukup', 1 => '1 · Kurang'];
@endphp

<div class="sg-container sg-inst-page">
    <x-ui.page-header title="Ulasan" description="Ulasan yang instansi Anda tulis untuk mahasiswa setelah proyek selesai." />

    @if ($stats['total'] > 0)
        <x-ui.summary label="Ringkasan ulasan" class="sg-inst-page__summary" :items="[
            ['value' => $stats['total'], 'label' => 'ulasan'],
            ['value' => number_format($stats['average_rating'], 1, ',', '.').'/5', 'label' => 'rata-rata rating'],
        ]" />

        <x-ui.filter-bar :action="route('institution.reviews.index')" search-label="Cari ulasan" search-placeholder="Cari nama mahasiswa"
                         :reset-href="route('institution.reviews.index')" :active="$activeFilters" id="filter-ulasan" class="sg-inst-page__filters">
            <x-ui.field label="Rating" for="rating">
                <x-ui.select name="rating" :options="$ratingOptions" :selected="request('rating')" placeholder="Semua rating" />
            </x-ui.field>
            <x-ui.field label="Urutkan" for="sort">
                <x-ui.select name="sort" :options="['latest' => 'Terbaru', 'oldest' => 'Terlama', 'rating_high' => 'Rating tertinggi', 'rating_low' => 'Rating terendah']" :selected="request('sort', 'latest')" />
            </x-ui.field>
        </x-ui.filter-bar>
    @endif

    @if ($reviews->count() > 0)
        <x-ui.list label="Daftar ulasan" class="sg-inst-reviews">
            @foreach ($reviews as $review)
                @php
                    $name = $review->reviewee?->display_name ?? 'Mahasiswa tidak ditemukan';
                    $editable = $review->created_at && ! $review->created_at->copy()->addDays(30)->isPast();
                @endphp
                <x-ui.list-item :title="$name" :href="route('institution.reviews.show', $review->id)"
                                :meta="collect([$review->project?->problem?->title ?? $review->project?->title, $review->reviewee?->student?->university?->name])->filter()->implode(' · ')">
                    <x-slot:leading><x-ui.avatar :name="$name" size="sm" decorative /></x-slot:leading>
                    @if (filled($review->review_text))
                        <p class="sg-inst-review-item__text">{{ $review->review_text }}</p>
                    @endif
                    <p class="sg-inst-review-item__foot">
                        <span>Ditulis {{ $tanggal($review->created_at) }}</span>
                        @if ($editable)
                            <a href="{{ route('institution.reviews.edit', $review->id) }}" class="sg-inst-link">Ubah<span class="sr-only"> ulasan untuk {{ $name }}</span></a>
                        @endif
                    </p>
                    <x-slot:end>@include('institution.reviews.components.rating', ['rating' => $review->rating])</x-slot:end>
                </x-ui.list-item>
            @endforeach
        </x-ui.list>

        <x-ui.pagination :paginator="$reviews" label="Halaman daftar ulasan" class="sg-inst-page__pagination" />
    @elseif ($filtered)
        <x-ui.empty-state illustration="no-search-results" title="Tidak ada ulasan yang cocok"
                          description="Ubah nama atau rating, atau tampilkan semua ulasan.">
            <x-slot:action>
                <x-ui.button variant="secondary" :href="route('institution.reviews.index')">Tampilkan semua ulasan</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state illustration="no-documents" title="Belum ada ulasan"
                          description="Ulasan dapat ditulis setelah proyek mahasiswa berstatus Selesai, dari dashboard atau halaman detail proyek.">
            <x-slot:action>
                <x-ui.button variant="secondary" :href="route('institution.projects.index', ['status' => 'completed'])">Lihat proyek selesai</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @endif
</div>
@endsection
