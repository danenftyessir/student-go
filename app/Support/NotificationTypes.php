<?php

namespace App\Support;

/**
 * phase 16: tipe notifikasi -> label bahasa indonesia dan ikon lucide untuk halaman /notifications.
 * hanya tipe yang ditulis backend (NotificationService, FriendController, NotificationsSeeder) atau sudah
 * dikenal model Notification; jangan menambah tipe. ikon sama dengan NOTIFICATION_ICONS di
 * resources/js/student-go/alpine.js (lonceng, phase 3) untuk tipe yang ada di sana.
 */
class NotificationTypes
{
    private const MAP = [
        'application_submitted' => ['Aplikasi masuk', 'send'],
        'application_accepted' => ['Aplikasi diterima', 'circle-check'],
        'application_rejected' => ['Aplikasi ditolak', 'circle-x'],
        'project_started' => ['Proyek dimulai', 'clipboard-list'],
        'project_milestone' => ['Milestone proyek', 'clipboard-list'],
        'report_submitted' => ['Laporan masuk', 'file-text'],
        'report_approved' => ['Laporan disetujui', 'clipboard-check'],
        'report_rejected' => ['Laporan ditolak', 'circle-x'],
        'review_received' => ['Ulasan diterima', 'award'],
        'problem_published' => ['Masalah baru', 'lightbulb'],
        'problem_closed' => ['Masalah ditutup', 'lock'],
        'message_received' => ['Pesan masuk', 'mail'],
        'deadline_reminder' => ['Pengingat tenggat', 'calendar'],
        'friend_request' => ['Permintaan pertemanan', 'user-plus'],
        'friend_accepted' => ['Pertemanan diterima', 'user-check'],
        'friend_activity' => ['Aktivitas teman', 'users'],
        'friend_suggestion' => ['Saran teman', 'users'],
        'mutual_connection' => ['Teman bersama', 'users'],
        'general' => ['Umum', 'info'],
    ];

    public static function label(?string $type): string
    {
        return self::MAP[$type][0] ?? 'Lainnya';
    }

    public static function icon(?string $type): string
    {
        return self::MAP[$type][1] ?? 'bell';
    }

    public static function known(?string $type): bool
    {
        return isset(self::MAP[$type]);
    }

    /**
     * opsi filter tipe: hanya tipe yang dimiliki pengguna (urutan sesuai MAP), tipe tak dikenal di akhir
     */
    public static function options(iterable $types): array
    {
        $types = collect($types)->filter()->unique();
        $known = collect(array_keys(self::MAP))->intersect($types);

        return $known->merge($types->diff($known)->sort())
            ->mapWithKeys(fn ($type) => [$type => self::label($type)])
            ->all();
    }

    /**
     * judul tanpa emoji. data di database tidak diubah; emoji dibuang saat render (J26).
     * ©, ® dan ™ bukan ikon, jadi tetap.
     */
    public static function cleanTitle(?string $title): string
    {
        $clean = preg_replace('/\s*(?:(?![\x{00A9}\x{00AE}\x{2122}])[\p{Extended_Pictographic}\x{FE0F}\x{200D}\x{20E3}\x{1F3FB}-\x{1F3FF}])+\s*/u', ' ', (string) $title);

        return trim(preg_replace('/\s{2,}/u', ' ', $clean ?? (string) $title));
    }
}
