// dialog native (<dialog class="sg-dialog">): buka/tutup, klik backdrop, dan dialog konfirmasi
// <dialog> memberi fokus awal, esc, latar inert dan pengembalian fokus ke pemicu.

function open(id) {
    const dialog = document.getElementById(id);
    if (dialog && typeof dialog.showModal === 'function' && !dialog.open) dialog.showModal();
}

export function initDialogs() {
    document.addEventListener('click', (e) => {
        const opener = e.target.closest('[data-sg-open]');
        if (opener) {
            e.preventDefault();
            open(opener.dataset.sgOpen);
            return;
        }
        const closer = e.target.closest('[data-sg-close]');
        if (closer) {
            closer.closest('dialog')?.close();
            return;
        }
        // klik di luar kotak dialog (backdrop) menutup dialog
        const d = e.target;
        if (d instanceof HTMLDialogElement && d.open && d.classList.contains('sg-dialog')) {
            const r = d.getBoundingClientRect();
            if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) d.close();
        }
    });
    window.addEventListener('sg-open-modal', (e) => open(e.detail));
    window.addEventListener('sg-close-modal', (e) => document.getElementById(e.detail)?.close());
}

// window.sgConfirm({ title, message, confirmLabel, cancelLabel, tone }) -> Promise<boolean>
// <form data-sg-confirm="pesan"> meminta konfirmasi sebelum submit (submitter tetap dikirim)
export function initConfirm() {
    const dialog = document.getElementById('sg-confirm');

    window.sgConfirm = (opts = {}) => new Promise((resolve) => {
        if (!dialog || typeof dialog.showModal !== 'function') {
            resolve(window.confirm(opts.message || opts.title || 'Lanjutkan?'));
            return;
        }
        dialog.querySelector('[data-sg-confirm-title]').textContent = opts.title || 'Konfirmasi tindakan';
        dialog.querySelector('[data-sg-confirm-message]').textContent = opts.message || '';
        const ok = dialog.querySelector('[data-sg-confirm-ok]');
        ok.textContent = opts.confirmLabel || 'Ya, lanjutkan';
        ok.className = `sg-btn ${opts.tone === 'primary' ? 'sg-btn--primary' : 'sg-btn--danger-solid'}`;
        dialog.querySelector('[data-sg-confirm-cancel]').textContent = opts.cancelLabel || 'Batal';
        dialog.returnValue = '';
        const onClose = () => {
            dialog.removeEventListener('close', onClose);
            resolve(dialog.returnValue === 'confirm');
        };
        dialog.addEventListener('close', onClose);
        dialog.showModal();
        // fokus awal pada pilihan aman
        dialog.querySelector('[data-sg-confirm-cancel]').focus();
    });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.sgConfirm) return;
        if (form.dataset.sgConfirmed) {
            delete form.dataset.sgConfirmed;
            return;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        const submitter = e.submitter && e.submitter.form === form ? e.submitter : undefined;
        window.sgConfirm({
            title: form.dataset.sgConfirmTitle,
            message: form.dataset.sgConfirm,
            confirmLabel: form.dataset.sgConfirmLabel,
            tone: form.dataset.sgConfirmTone,
        }).then((confirmed) => {
            if (!confirmed) return;
            form.dataset.sgConfirmed = '1';
            form.requestSubmit(submitter);
        });
    }, true);
}

// form POST: tombol pengirim menampilkan status proses dan submit kedua ditahan (cegah data ganda).
// submit yang sudah dibatalkan (fetch/alpine .prevent, konfirmasi belum dijawab) tidak disentuh.
// <form data-sg-no-busy> untuk keluar dari perilaku ini.
function resetBusy(form) {
    delete form.dataset.sgBusy;
    form.querySelectorAll('[data-sg-busy-btn]').forEach((btn) => {
        btn.disabled = false;
        btn.removeAttribute('aria-busy');
        btn.removeAttribute('data-loading');
        btn.removeAttribute('data-sg-busy-btn');
        btn.querySelector(':scope > .sg-spinner[data-sg-busy-spinner]')?.remove();
        btn.querySelectorAll(':scope > [data-sg-busy-hidden]').forEach((el) => { el.removeAttribute('hidden'); el.removeAttribute('data-sg-busy-hidden'); });
    });
}

export function initSubmitFeedback() {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || e.defaultPrevented) return;
        if (form.method.toLowerCase() !== 'post' || form.hasAttribute('data-sg-no-busy')) return;
        if (form.dataset.sgBusy) {
            e.preventDefault();
            return;
        }
        form.dataset.sgBusy = '1';
        const btn = e.submitter instanceof HTMLButtonElement ? e.submitter : null;
        if (!btn) return;
        btn.setAttribute('data-sg-busy-btn', '');
        btn.setAttribute('aria-busy', 'true');
        if (btn.classList.contains('sg-btn')) {
            btn.setAttribute('data-loading', '');
            const icon = btn.querySelector(':scope > .sg-icon:not([hidden])');
            if (icon) { icon.setAttribute('hidden', ''); icon.setAttribute('data-sg-busy-hidden', ''); }
            btn.insertAdjacentHTML('afterbegin', '<span class="sg-spinner" data-sg-busy-spinner aria-hidden="true"></span>');
        }
        // dinonaktifkan setelah submit berjalan agar name/value tombol tetap ikut terkirim
        setTimeout(() => { btn.disabled = true; }, 0);
    });
    // kembali lewat tombol back (bfcache): form bisa dipakai lagi
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) document.querySelectorAll('form[data-sg-busy]').forEach(resetBusy);
    });
}
