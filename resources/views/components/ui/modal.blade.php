{{-- dialog modal (native <dialog>): fokus masuk ke dialog, esc menutup, latar inert, fokus kembali ke pemicu.
     buka: <button data-sg-open="{id}"> atau $dispatch('sg-open-modal', '{id}'); tutup: data-sg-close, esc, klik latar.
     drawer = panel samping. slot footer = tombol aksi. --}}
@props(['id', 'title', 'drawer' => false])
<dialog id="{{ $id }}" {{ $attributes->class(['sg-dialog', 'sg-drawer' => $drawer]) }} aria-labelledby="{{ $id }}-title">
    <div class="sg-dialog__head">
        <h2 id="{{ $id }}-title">{{ $title }}</h2>
        <button type="button" class="sg-icon-btn" data-sg-close aria-label="Tutup"><x-icon name="x" /></button>
    </div>
    <div class="sg-dialog__body">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="sg-dialog__foot">{{ $footer }}</div>
    @endisset
</dialog>
