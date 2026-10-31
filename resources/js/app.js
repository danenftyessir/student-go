// student go: perilaku bersama shell dan komponen (phase 3). tidak ada dependensi baru;
// alpine tetap dimuat dari cdn (D-20). bundel ini dimuat di <head> sebelum alpine (defer),
// sehingga pendaftaran alpine:init selalu terpasang lebih dulu.
import { registerAlpine } from './student-go/alpine';
import { NotificationManager } from './student-go/toast';
import { initConfirm, initDialogs } from './student-go/dialog';
import { initAvatars, initScrollTop } from './student-go/misc';
import { initHeroVideo } from './student-go/hero-video';
import { registerAuth } from './student-go/auth';

document.addEventListener('alpine:init', () => {
    registerAlpine(window.Alpine);
    // phase 6: halaman masuk, registrasi dan reset password
    registerAuth(window.Alpine);
});

const ready = (fn) => (document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn());

ready(() => {
    // api lama tetap tersedia untuk halaman yang memanggilnya
    const notificationManager = new NotificationManager();
    window.notificationManager = notificationManager;
    window.showNotification = (message, type, duration) => notificationManager.show(message, type, duration);

    initDialogs();
    initConfirm();
    initAvatars();
    initScrollTop();
    initHeroVideo();

    // phase 21: chart.js lokal dimuat (chunk terpisah) hanya bila halaman punya grafik
    if (document.querySelector('canvas[data-sg-chart]')) {
        import('./student-go/charts').then(({ initCharts }) => initCharts());
    }
});
