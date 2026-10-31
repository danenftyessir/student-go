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
