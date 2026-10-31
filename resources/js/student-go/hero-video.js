// pembuka beranda (AD-11): video latar berulang tanpa suara di belakang judul. poster tampil lebih dulu;
// video baru dimuat bila gerak tidak dikurangi dan hemat data mati.
// juga mengganti header transparan -> padat setelah pembuka lewat (satu intersectionobserver, tanpa scroll listener).
import { prefersReducedMotion } from './util';

// berkas ponsel atau desktop; webm (vp9) bila pasti didukung, selain itu mp4
function pickSource(hero, video) {
    const size = window.matchMedia('(max-width: 767px)').matches ? 'Mobile' : 'Desktop';
    const type = video.canPlayType('video/webm; codecs="vp9"') === 'probably' ? 'Webm' : 'Mp4';
    return hero.dataset[`video${size}${type}`];
}

function initHeader(hero) {
    const header = document.querySelector('[data-sg-header]');
    const sentinel = hero.querySelector('[data-sg-hero-sentinel]');
    if (!header || !sentinel || !('IntersectionObserver' in window)) return;

    const height = header.offsetHeight || 64;
    new IntersectionObserver(([entry]) => {
        // penanda sudah lewat di bawah header = pembuka tidak lagi di belakang header
        const passed = !entry.isIntersecting && entry.boundingClientRect.top < height;
        header.classList.toggle('is-over-hero', !passed);
    }, { rootMargin: `-${height}px 0px 0px 0px` }).observe(sentinel);
}

function initVideo(hero) {
    const video = hero.querySelector('.sg-hero__video');
    if (!video) return;

    // gerak dikurangi atau hemat data: poster saja, video tidak pernah dimuat
    if (prefersReducedMotion() || navigator.connection?.saveData === true) return;

    let loaded = false;
    let inView = true;
    let failed = false;

    const load = () => {
        if (loaded) return;
        const src = pickSource(hero, video);
        if (!src) return;
        loaded = true;
        video.src = src;
        video.load();
    };

    const play = () => {
        if (failed || !inView || document.hidden) return;
        load();
        const start = () => {
            // penolakan autoplay (mis. mode hemat daya): poster tetap
            video.play()?.catch(() => {});
        };
        if (video.readyState >= 3) start();
        else video.addEventListener('canplay', start, { once: true });
    };

    video.addEventListener('playing', () => hero.classList.add('has-video'));
    video.addEventListener('error', () => {
        // berkas gagal: poster tetap, tidak ada yang lain rusak
        failed = true;
        hero.classList.remove('has-video');
    });

    // jeda saat tab tersembunyi atau pembuka terlihat kurang dari 25 persen; lanjut bila kembali
    document.addEventListener('visibilitychange', () => (document.hidden ? video.pause() : play()));
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            inView = entry.intersectionRatio >= 0.25;
            if (inView) play();
            else if (!video.paused) video.pause();
        }, { threshold: [0, 0.25] }).observe(hero);
    }

    play();
}

export function initHeroVideo() {
    const hero = document.querySelector('[data-sg-hero]');
    if (!hero) return;
    initHeader(hero);
    initVideo(hero);
}
