{{-- tolak: POST institution.applications.reject; feedback dan rejection_reason wajib (aturan server: maks. 1000 / 500).
     rejection_reason divalidasi tetapi tidak punya kolom di tabel applications (tidak tersimpan); bantuan menyatakannya apa adanya. --}}
<form method="POST" action="{{ route('institution.applications.reject', $application->id) }}" id="{{ $formId }}" class="sg-inst-form">
    @csrf
    <x-ui.field label="Masukan untuk mahasiswa" for="{{ $formId }}-feedback" name="feedback" required
                help="Dikirim ke mahasiswa bersama pemberitahuan penolakan.">
        <x-ui.textarea name="feedback" :id="$formId.'-feedback'" rows="3" maxlength="1000" required aria-describedby="{{ $formId }}-feedback-help" />
    </x-ui.field>
    <x-ui.field label="Alasan penolakan (catatan singkat)" for="{{ $formId }}-reason" name="rejection_reason" required
                help="Wajib diisi. Alasan ini belum disimpan oleh sistem; yang tersimpan dan terlihat oleh mahasiswa adalah masukan di atas.">
        <x-ui.textarea name="rejection_reason" :id="$formId.'-reason'" rows="2" maxlength="500" required aria-describedby="{{ $formId }}-reason-help" />
    </x-ui.field>
</form>
