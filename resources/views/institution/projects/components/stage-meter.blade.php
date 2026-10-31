{{-- phase 19: meter tahap dari milestone (disetujui user): satu segmen per milestone, terisi = selesai.
     sama dengan aturan Project::updateProgress dan halaman detail; angka persen tersimpan tidak dipakai di sini. --}}
@php
    $total = $milestones->count();
    $done = $milestones->where('status', 'completed')->count();
@endphp
@if ($total > 0)
    <span class="sg-inst-stages">
        <span class="sg-inst-stages__bars" aria-hidden="true">
            @for ($i = 1; $i <= $total; $i++)<i @if ($i <= $done) data-done @endif></i>@endfor
        </span>
        {{ $done }} dari {{ $total }} tahap
    </span>
@else
    <span class="sg-inst-detail__note">Belum ada tahap</span>
@endif
