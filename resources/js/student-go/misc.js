// avatar: foto gagal dimuat -> inisial; tautan berkas storage dicek dulu; tombol kembali ke atas
import { prefersReducedMotion } from './util';

function fallbackToInitials(img) {
    const box = img.closest('.sg-avatar');
    if (!box) return;
    box.textContent = box.dataset.initials || '';
    if (box.dataset.label) {
        box.setAttribute('role', 'img');
        box.setAttribute('aria-label', box.dataset.label);
    }
}

export function initAvatars() {
    // gambar yang sudah gagal sebelum skrip ini berjalan
    document.querySelectorAll('.sg-avatar img').forEach((img) => {
        if (img.complete && img.naturalWidth === 0) fallbackToInitials(img);
    });
    document.addEventListener('error', (e) => {
        if (e.target instanceof HTMLImageElement && e.target.closest('.sg-avatar')) fallbackToInitials(e.target);
    }, true);
}

// tautan berkas di storage (<a data-sg-file>): dicek dulu sebelum dibuka di tab baru.
// berkas yang belum diunggah menampilkan pesan, bukan halaman json galat dari supabase.
export function initFileLinks() {
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('a[data-sg-file]');
        if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        // tab dibuka langsung pada klik agar tidak diblokir sebagai popup
        const tab = window.open('', '_blank');
        try {
            const response = await fetch(link.href, { method: 'HEAD' });
            if (!response.ok) throw new Error(String(response.status));
            if (tab) {
                tab.opener = null;
                tab.location.href = link.href;
            } else {
                window.location.href = link.href;
            }
        } catch (error) {
            tab?.close();
            window.showNotification?.('Berkas ini belum tersedia di penyimpanan.', 'error');
        }
    });
}

export function initScrollTop() {
    const btn = document.getElementById('scroll-to-top');
    if (!btn) return;
    const update = () => { btn.hidden = window.scrollY <= 300; };
    window.addEventListener('scroll', update, { passive: true });
    update();
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
        // tombol tersembunyi setelah di atas: pindahkan fokus ke awal halaman
        document.querySelector('.sg-header__brand')?.focus({ preventScroll: true });
    });
}
