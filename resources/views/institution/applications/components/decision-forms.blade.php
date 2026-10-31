{{-- phase 19: formulir keputusan Terima / Tolak. route, method POST dan nama field sama seperti sebelumnya:
     terima = feedback (opsional), notes (opsional); tolak = feedback (wajib), rejection_reason (wajib).
     modal = true -> dua <dialog> (x-ui.modal) yang dibuka tombol bar keputusan; false -> dua formulir di halaman. --}}
@php
    $prefix = 'lamaran-'.$application->id;
    $acceptId = $prefix.'-terima';
    $rejectId = $prefix.'-tolak';
@endphp

@if ($modal)
    <x-ui.modal :id="$acceptId" title="Terima lamaran">
        @include('institution.applications.components.accept-form', ['formId' => $acceptId.'-form'])
        <x-slot:footer>
            <x-ui.button variant="tertiary" data-sg-close>Batal</x-ui.button>
            <x-ui.button type="submit" form="{{ $acceptId }}-form">Terima lamaran</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.modal :id="$rejectId" title="Tolak lamaran">
        @include('institution.applications.components.reject-form', ['formId' => $rejectId.'-form'])
        <x-slot:footer>
            <x-ui.button variant="tertiary" data-sg-close>Batal</x-ui.button>
            <x-ui.button type="submit" variant="danger-solid" form="{{ $rejectId }}-form">Tolak lamaran</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@else
    <section class="sg-inst-detail__block" aria-labelledby="{{ $acceptId }}-judul">
        <h2 id="{{ $acceptId }}-judul">Terima lamaran</h2>
        @include('institution.applications.components.accept-form', ['formId' => $acceptId.'-form'])
        <div class="sg-inst-decision__actions">
            <x-ui.button type="submit" form="{{ $acceptId }}-form">Terima lamaran</x-ui.button>
        </div>
    </section>

    <section class="sg-inst-detail__block" aria-labelledby="{{ $rejectId }}-judul">
        <h2 id="{{ $rejectId }}-judul">Tolak lamaran</h2>
        @include('institution.applications.components.reject-form', ['formId' => $rejectId.'-form'])
        <div class="sg-inst-decision__actions">
            <x-ui.button type="submit" variant="danger" form="{{ $rejectId }}-form">Tolak lamaran</x-ui.button>
        </div>
    </section>
@endif
