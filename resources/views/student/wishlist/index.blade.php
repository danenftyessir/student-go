{{-- resources/views/student/wishlist/index.blade.php --}}
{{-- halaman wishlist/saved problems --}}

@extends('layouts.app')

@section('title', 'Wishlist Saya')

@section('content')
@php
    // phase 11: masalah tersimpan sebagai baris: judul (tautan detail tetap), instansi, tenggat, "Lamar" ke
    // student.applications.create (syarat lamar tetap di controller) dan "Hapus" (DELETE /student/wishlist/{id}/remove, JSON).
    $tanggal = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('j M Y') : null;
    $problemIds = $wishlists->pluck('problem_id')->filter()->all();
    // status lamaran yang sudah ada untuk masalah di halaman ini (hanya baca)
    $lamaran = $problemIds
        ? \App\Models\Application::where('student_id', auth()->user()->student->id)->whereIn('problem_id', $problemIds)->pluck('status', 'problem_id')
        : collect();
@endphp

<div class="sg-container pb-16">
    <x-ui.page-header title="Wishlist Saya" description="Masalah yang Anda simpan untuk dibaca lagi atau dilamar nanti.">
        @if ($wishlists->total() > 0)
            <x-slot:actions>
                <x-ui.button variant="secondary" icon="search" :href="route('student.browse-problems.index')">Telusuri masalah</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($wishlists->isEmpty())
        <x-ui.empty-state illustration="no-saved-projects" title="Belum ada masalah tersimpan"
                          description="Simpan masalah dari halaman detailnya agar mudah ditemukan saat Anda siap melamar.">
            <x-slot:action>
                <x-ui.button :href="route('student.browse-problems.index')">Telusuri masalah KKN</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @else
        <x-ui.summary label="Ringkasan wishlist" class="mb-4" :items="[['value' => $wishlists->total(), 'label' => 'masalah tersimpan']]" />

        <x-ui.table caption="Masalah tersimpan">
            <x-slot:head>
                <th scope="col">Masalah</th>
                <th scope="col">Instansi</th>
                <th scope="col">Tenggat</th>
                <th scope="col"><span class="sr-only">Aksi</span></th>
            </x-slot:head>
            @foreach ($wishlists as $wishlist)
                @php
                    $problem = $wishlist->problem;
                    $lokasi = collect([$problem?->regency?->name, $problem?->province?->name])->filter()->implode(', ');
                    $deadline = $problem?->application_deadline ? \Illuminate\Support\Carbon::parse($problem->application_deadline) : null;
                    $dibuka = $problem && $problem->status === 'open' && $deadline && $deadline->copy()->endOfDay()->isFuture();
                    $statusLamaran = $problem ? ($lamaran[$problem->id] ?? null) : null;
                @endphp
                <tr data-wishlist-row="{{ $wishlist->id }}">
                    <td data-label="Masalah">
                        <div>
                            @if ($problem)
                                <a href="{{ route('student.browse-problems.show', $problem->id) }}" class="sg-table__primary">{{ $problem->title }}</a>
                            @else
                                <span class="sg-table__primary">Masalah tidak tersedia</span>
                            @endif
                            @if ($lokasi !== '')<span class="sg-table__meta">{{ $lokasi }}</span>@endif
                            @if (filled($wishlist->notes))
                                <p class="mt-2 mb-0 max-w-prose rounded-sm bg-gray-50 px-2.5 py-1.5 text-xs leading-4 text-gray-600 wrap-break-word">
                                    <span class="font-semibold text-gray-900">Catatan saya:</span> {{ $wishlist->notes }}
                                </p>
                            @endif
                        </div>
                    </td>
                    <td data-label="Instansi"><span>{{ $problem?->institution?->name ?? '—' }}</span></td>
                    <td data-label="Tenggat" class="whitespace-nowrap tabular-nums">
                        <div>
                            <span class="block">{{ $deadline ? $tanggal($deadline) : '—' }}</span>
                            @if ($deadline)
                                <span class="sg-table__meta">{{ $dibuka ? $deadline->locale('id')->diffForHumans() : 'Pendaftaran ditutup' }}</span>
                            @endif
                        </div>
                    </td>
                    <td data-label="Aksi">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($statusLamaran)
                                <x-ui.status domain="application" :value="$statusLamaran" />
                            @elseif ($dibuka)
                                <x-ui.button variant="secondary" :href="route('student.applications.create', $problem->id)" class="whitespace-nowrap">Lamar</x-ui.button>
                            @endif
                            <button type="button" class="sg-btn sg-btn--tertiary whitespace-nowrap"
                                    data-wishlist-remove="{{ $wishlist->id }}" data-title="{{ $problem?->title }}"
                                    onclick="removeFromWishlist(this)">
                                <x-icon name="trash-2" size="sm" />Hapus<span class="sr-only"> {{ $problem?->title }} dari wishlist</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>

        <x-ui.pagination :paginator="$wishlists" label="Halaman wishlist" class="mt-4" />
    @endif
</div>
@endsection

@push('scripts')
<script>
// hapus dari wishlist: konfirmasi lewat dialog bersama, endpoint dan metode tetap (DELETE, JSON)
async function removeFromWishlist(button) {
    const wishlistId = button.dataset.wishlistRemove;
    const confirmed = await window.sgConfirm({
        title: 'Hapus dari wishlist?',
        message: `“${button.dataset.title}” akan dihapus dari wishlist. Masalahnya sendiri tidak terpengaruh.`,
        confirmLabel: 'Ya, hapus',
    });
    if (!confirmed) return;

    button.disabled = true;
    try {
        const response = await fetch(`/student/wishlist/${wishlistId}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (data.success) {
            // muat ulang supaya jumlah, halaman dan keadaan kosong tetap benar (perilaku lama)
            window.location.reload();
        } else {
            window.showNotification(data.message || 'Gagal menghapus dari wishlist', 'error');
            button.disabled = false;
        }
    } catch (error) {
        window.showNotification('Terjadi kesalahan saat menghapus dari wishlist', 'error');
        button.disabled = false;
    }
}
</script>
@endpush
