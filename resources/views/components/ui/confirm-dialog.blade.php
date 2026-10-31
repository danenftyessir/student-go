{{-- satu dialog konfirmasi bersama (dirender sekali di layouts.app). dipakai lewat:
     <form data-sg-confirm="Hapus dokumen ini?" data-sg-confirm-label="Hapus"> … atau window.sgConfirm({...}).
     fokus awal pada "Batal"; esc/klik latar = batal. teks diisi resources/js/student-go/dialog.js. --}}
<dialog id="sg-confirm" class="sg-dialog" aria-labelledby="sg-confirm-title" aria-describedby="sg-confirm-message">
    <form method="dialog">
        <div class="sg-dialog__head">
            <h2 id="sg-confirm-title" data-sg-confirm-title>Konfirmasi tindakan</h2>
        </div>
        <div class="sg-dialog__body">
            <p id="sg-confirm-message" data-sg-confirm-message></p>
        </div>
        <div class="sg-dialog__foot">
            <button type="submit" value="cancel" class="sg-btn sg-btn--secondary" data-sg-confirm-cancel>Batal</button>
            <button type="submit" value="confirm" class="sg-btn sg-btn--danger-solid" data-sg-confirm-ok>Ya, lanjutkan</button>
        </div>
    </form>
</dialog>
