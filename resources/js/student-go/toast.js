// toast: flash message session + window.showNotification(message, type, duration)
// api lama dipertahankan (notificationManager.show, showNotification); pesan dirender sebagai teks.
import { iconSvg } from './util';

const ICONS = { success: 'circle-check', error: 'circle-x', info: 'info', warning: 'triangle-alert' };

export class NotificationManager {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        this.showExistingNotifications();
    }

    createContainer() {
        if (this.container) return this.container;
        const existing = document.getElementById('notification-container');
        if (existing) {
            this.container = existing;
            return existing;
        }
        const el = document.createElement('div');
        el.id = 'notification-container';
        el.className = 'sg-toasts';
        el.setAttribute('aria-live', 'polite');
        document.body.appendChild(el);
        this.container = el;
        return el;
    }

    show(message, type = 'info', duration = 5000) {
        const tone = ICONS[type] ? type : 'info';
        const toast = document.createElement('div');
        toast.className = 'sg-overlay sg-toast';
        toast.dataset.tone = tone;
        if (tone === 'error') toast.setAttribute('role', 'alert');
        toast.innerHTML = `${iconSvg(ICONS[tone])}<p class="sg-toast__msg"></p>`
            + `<button type="button" class="sg-icon-btn" aria-label="Tutup pesan">${iconSvg('x', 'sm')}</button>`;
        toast.querySelector('.sg-toast__msg').textContent = String(message ?? '');
        toast.querySelector('button').addEventListener('click', () => toast.remove());
        this.createContainer().appendChild(toast);

        if (duration > 0) {
            // jeda saat kursor/fokus berada di toast agar sempat dibaca
            let timer = setTimeout(() => this.dismiss(toast), duration);
            const pause = () => clearTimeout(timer);
            const resume = () => { timer = setTimeout(() => this.dismiss(toast), duration); };
            toast.addEventListener('mouseenter', pause);
            toast.addEventListener('mouseleave', resume);
            toast.addEventListener('focusin', pause);
            toast.addEventListener('focusout', resume);
        }
        return toast;
    }

    dismiss(toast) {
        toast.dataset.leaving = '';
        setTimeout(() => toast.remove(), 200);
    }

    // flash message dari session (success, error, info, warning), ditulis layout ke #sg-flash
    showExistingNotifications() {
        const node = document.getElementById('sg-flash');
        if (!node) return;
        let flash = {};
        try {
            flash = JSON.parse(node.textContent || '{}');
        } catch (e) {
            flash = {};
        }
        ['success', 'error', 'info', 'warning'].forEach((type) => {
            if (flash[type]) this.show(flash[type], type);
        });
    }
}
