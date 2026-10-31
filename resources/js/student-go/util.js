// utilitas kecil bersama: sprite ikon, fokus, ukuran berkas

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function spriteUrl() {
    return document.querySelector('meta[name="sg-sprite"]')?.content || '/assets/icons/ui/sprite.svg';
}

// svg ikon dari sprite lokal (dekoratif)
export function iconSvg(name, size = '') {
    const cls = size ? `sg-icon sg-icon--${size}` : 'sg-icon';
    return `<svg class="${cls}" aria-hidden="true" focusable="false"><use href="${spriteUrl()}#${name}"></use></svg>`;
}

export function focusables(root) {
    return [...root.querySelectorAll(FOCUSABLE)].filter((el) => el.getClientRects().length > 0);
}

// kunci fokus di dalam root (drawer); kembalikan fungsi pelepas
export function trapFocus(root) {
    const onKeydown = (e) => {
        if (e.key !== 'Tab') return;
        const items = focusables(root);
        if (!items.length) return;
        const first = items[0];
        const last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    };
    root.addEventListener('keydown', onKeydown);
    return () => root.removeEventListener('keydown', onKeydown);
}

// "1,4 MB" (format indonesia)
export function formatBytes(bytes) {
    if (!Number.isFinite(bytes)) return '';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    let n = bytes;
    while (n >= 1024 && i < units.length - 1) {
        n /= 1024;
        i++;
    }
    return `${n.toLocaleString('id-ID', { maximumFractionDigits: i === 0 ? 0 : 1 })} ${units[i]}`;
}

export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

export function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
