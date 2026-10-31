// avatar: foto gagal dimuat -> inisial; tombol kembali ke atas
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
