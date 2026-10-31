{{-- terima: POST institution.applications.accept; feedback dan notes opsional (aturan server: maks. 1000 / 500) --}}
<form method="POST" action="{{ route('institution.applications.accept', $application->id) }}" id="{{ $formId }}" class="sg-inst-form">
    @csrf
    <p class="sg-inst-detail__note">
        {{ $application->student->display_name }} diterima untuk “{{ $application->problem->title }}”. Proyek dibuat otomatis dan mahasiswa menerima notifikasi.
    </p>
    <x-ui.field label="Masukan untuk mahasiswa (opsional)" for="{{ $formId }}-feedback" name="feedback">
        <x-ui.textarea name="feedback" :id="$formId.'-feedback'" rows="3" maxlength="1000" placeholder="Arahan atau ucapan selamat untuk mahasiswa" />
    </x-ui.field>
    <x-ui.field label="Catatan internal (opsional)" for="{{ $formId }}-notes" name="notes" help="Tidak terlihat oleh mahasiswa.">
        <x-ui.textarea name="notes" :id="$formId.'-notes'" rows="2" maxlength="500" aria-describedby="{{ $formId }}-notes-help" />
    </x-ui.field>
</form>
