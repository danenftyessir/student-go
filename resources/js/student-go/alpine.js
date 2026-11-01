// komponen alpine bersama (phase 3). alpine tetap dari cdn (D-20); didaftarkan saat alpine:init.
import { csrfToken, focusables, formatBytes, trapFocus } from './util';

const NOTIFICATION_ICONS = {
    application_submitted: 'send',
    project_applied: 'send',
    application_accepted: 'circle-check',
    application_rejected: 'circle-x',
    report_submitted: 'file-text',
    report_approved: 'clipboard-check',
    review_received: 'award',
    project_started: 'clipboard-list',
    project_update: 'clipboard-list',
    project_milestone: 'clipboard-list',
    project_completed: 'check',
    problem_published: 'lightbulb',
    friend_request: 'user-plus',
    friend_accepted: 'user-check',
    friend_suggestion: 'users',
    friend_activity: 'users',
    message: 'mail',
};

function matchesAccept(file, accept) {
    if (!accept) return true;
    const name = file.name.toLowerCase();
    return accept.split(',').map((s) => s.trim().toLowerCase()).some((rule) => {
        if (rule.startsWith('.')) return name.endsWith(rule);
        if (rule.endsWith('/*')) return file.type.startsWith(rule.slice(0, -1));
        return file.type === rule;
    });
}

export function registerAlpine(Alpine) {
    // dropdown / menu: pola disclosure (tab berpindah antar item, panah atas/bawah, esc menutup)
    Alpine.data('sgDisclosure', () => ({
        open: false,
        toggle() {
            this.open ? this.close() : (this.open = true);
        },
        close(returnFocus = false) {
            if (!this.open) return;
            this.open = false;
            if (returnFocus) this.$refs.trigger?.focus();
        },
        onFocusOut(e) {
            if (this.open && e.relatedTarget && !this.$root.contains(e.relatedTarget)) this.close();
        },
        move(e, dir) {
            const panel = this.$refs.panel;
            if (!panel) return;
            if (!this.open) this.open = true;
            this.$nextTick(() => {
                const items = [...panel.querySelectorAll('.sg-menu__item')].filter((el) => el.getClientRects().length);
                if (!items.length) return;
                const i = items.indexOf(document.activeElement);
                const next = i === -1 ? (dir > 0 ? 0 : items.length - 1) : (i + dir + items.length) % items.length;
                items[next].focus();
            });
            e.preventDefault();
        },
    }));

    // menu mobile (< 1024px)
    Alpine.data('sgMobileNav', () => ({
        open: false,
        init() {
            const mq = window.matchMedia('(min-width: 1024px)');
            mq.addEventListener('change', (e) => { if (e.matches) this.open = false; });
        },
        toggle() {
            this.open = !this.open;
        },
        close(returnFocus = false) {
            if (!this.open) return;
            this.open = false;
            if (returnFocus) this.$refs.toggle?.focus();
        },
    }));

    // hanya tautan http(s) atau relatif yang boleh dibuka dari notifikasi
    const actionUrl = (value) => {
        if (!value || typeof value !== 'string') return null;
        try {
            const url = new URL(value, window.location.origin);
            return ['http:', 'https:'].includes(url.protocol) ? url.href : null;
        } catch (error) {
            return null;
        }
    };

    // lonceng notifikasi: endpoint sama seperti sebelumnya (5 terbaru, polling 30 detik,
    // klik menandai dibaca lalu membuka action_url bila ada, tandai semua dibaca)
    Alpine.data('sgNotifications', (cfg) => ({
        open: false,
        notifications: [],
        unreadCount: 0,
        loaded: false,
        get label() {
            if (!this.loaded) return 'Notifikasi';
            return this.unreadCount > 0
                ? `Notifikasi, ${this.unreadCount} belum dibaca`
                : 'Notifikasi, tidak ada yang belum dibaca';
        },
        get liveText() {
            return this.loaded && this.unreadCount > 0 ? `${this.unreadCount} notifikasi belum dibaca` : '';
        },
        async init() {
            await this.loadNotifications();
            setInterval(() => this.loadNotifications(), 30000);
        },
        async toggle() {
            if (this.open) {
                this.close();
                return;
            }
            this.open = true;
            await this.loadNotifications();
        },
        close(returnFocus = false) {
            if (!this.open) return;
            this.open = false;
            if (returnFocus) this.$refs.trigger?.focus();
        },
        onFocusOut(e) {
            if (this.open && e.relatedTarget && !this.$root.contains(e.relatedTarget)) this.close();
        },
        async loadNotifications() {
            try {
                const response = await fetch(cfg.latestUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                });
                const data = await response.json();
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
                this.loaded = true;
                window.dispatchEvent(new CustomEvent('sg-notifications', { detail: { unread: this.unreadCount } }));
            } catch (error) {
                console.error('gagal load notifikasi:', error);
            }
        },
        async handleNotificationClick(notification) {
            if (!notification.is_read) {
                try {
                    await fetch(cfg.readUrl.replace('__ID__', notification.id), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken(),
                        },
                    });
                    await this.loadNotifications();
                } catch (error) {
                    console.error('gagal mark as read:', error);
                }
            }
            // sama seperti tombol "Buka" di halaman notifikasi: buka action_url bila ada
            const target = actionUrl(notification.action_url);
            if (target) {
                window.location.href = target;
                return;
            }
            this.close(true);
        },
        async markAllAsRead() {
            try {
                const response = await fetch(cfg.readAllUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });
                if (response.ok) await this.loadNotifications();
            } catch (error) {
                console.error('gagal mark all as read:', error);
            }
        },
        icon(type) {
            return `${cfg.sprite}#${NOTIFICATION_ICONS[type] || 'bell'}`;
        },
        formatTime(dateString) {
            const date = new Date(dateString);
            const diff = Math.floor((new Date() - date) / 1000);
            if (diff < 60) return 'Baru saja';
            if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`;
            if (diff < 604800) return `${Math.floor(diff / 86400)} hari yang lalu`;
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        },
    }));

    // upload: dropzone + daftar berkas (nama, ukuran, hapus). input file asli tetap yang dikirim.
    Alpine.data('sgUpload', (cfg = {}) => ({
        files: [],
        drag: false,
        error: '',
        init() {
            this.sync();
        },
        onDrop(e) {
            this.drag = false;
            const dropped = [...(e.dataTransfer?.files || [])];
            if (!dropped.length) return;
            const picked = cfg.multiple ? dropped : dropped.slice(0, 1);
            const rejected = picked.find((f) => !matchesAccept(f, cfg.accept));
            if (rejected) {
                this.error = `Format ${rejected.name} tidak didukung.`;
                return;
            }
            const dt = new DataTransfer();
            picked.forEach((f) => dt.items.add(f));
            this.$refs.input.files = dt.files;
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },
        remove(index) {
            const input = this.$refs.input;
            const dt = new DataTransfer();
            [...input.files].forEach((f, i) => { if (i !== index) dt.items.add(f); });
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            input.focus();
        },
        sync() {
            const list = [...(this.$refs.input?.files || [])];
            this.files = list.map((f) => ({ name: f.name, size: formatBytes(f.size) }));
            const tooBig = cfg.maxSize ? list.find((f) => f.size > cfg.maxSize) : null;
            this.error = tooBig ? `${tooBig.name} melebihi batas ${formatBytes(cfg.maxSize)}.` : '';
        },
    }));

    // tab dalam halaman: panah kiri/kanan, home, end (roving tabindex)
    Alpine.data('sgTabs', (initial) => ({
        selected: initial,
        select(id) {
            this.selected = id;
        },
        onKeydown(e) {
            const tabs = [...this.$refs.tablist.querySelectorAll('[role="tab"]')];
            const i = tabs.indexOf(document.activeElement);
            if (i === -1) return;
            const keys = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
            if (!(e.key in keys)) return;
            e.preventDefault();
            const next = tabs[(keys[e.key] + tabs.length) % tabs.length];
            next.focus();
            this.select(next.dataset.tab);
        },
    }));

    // filter bar: di bawah 768px panel filter menjadi drawer (fokus terkunci, esc menutup)
    Alpine.data('sgFilterBar', () => ({
        open: false,
        release: null,
        init() {
            const mq = window.matchMedia('(min-width: 768px)');
            mq.addEventListener('change', (e) => { if (e.matches) this.close(false); });
        },
        show() {
            this.open = true;
            this.$nextTick(() => {
                this.release = trapFocus(this.$refs.panel);
                focusables(this.$refs.panel)[0]?.focus();
            });
        },
        close(returnFocus = true) {
            if (!this.open) return;
            this.open = false;
            this.release?.();
            this.release = null;
            if (returnFocus) this.$refs.toggle?.focus();
        },
    }));
}
