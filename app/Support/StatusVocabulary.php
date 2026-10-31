<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * satu-satunya pemetaan status (D-02, disetujui 2026-10-30): nilai yang
 * tersimpan di database -> label bahasa indonesia, nada warna dan ikon lucide.
 * nilai diambil dari migrasi (DISCOVERY_REPORT S1-20); jangan menambah status
 * yang tidak ada di database. dipakai oleh <x-ui.status>.
 */
class StatusVocabulary
{
    // nada visual: neutral, info, success, warning, danger, muted (lihat .sg-status[data-tone])
    private const MAP = [
        'application' => [
            'pending' => ['Menunggu', 'neutral', 'hourglass'],
            'reviewed' => ['Ditinjau', 'info', 'eye'],
            'accepted' => ['Diterima', 'success', 'circle-check'],
            'rejected' => ['Ditolak', 'danger', 'circle-x'],
        ],
        'job_application' => [
            'pending' => ['Menunggu', 'neutral', 'hourglass'],
            'reviewed' => ['Ditinjau', 'info', 'eye'],
            'shortlisted' => ['Terpilih', 'info', 'user-check'],
            'accepted' => ['Diterima', 'success', 'circle-check'],
            'rejected' => ['Ditolak', 'danger', 'circle-x'],
            // phase 21: nilai yang ditulis pipeline perusahaan (validasi JobApplicationController, Student\JobController::quickApply)
            'new' => ['Menunggu', 'neutral', 'hourglass'],
            'reviewing' => ['Ditinjau', 'info', 'eye'],
            'interview' => ['Wawancara', 'info', 'message-square-text'],
            'offer' => ['Penawaran', 'info', 'send'],
            'hired' => ['Direkrut', 'success', 'circle-check'],
        ],
        'problem' => [
            'draft' => ['Draf', 'neutral', 'pencil'],
            'open' => ['Dibuka', 'success', 'send'],
            'in_progress' => ['Berjalan', 'info', 'refresh-cw'],
            'completed' => ['Selesai', 'success', 'check'],
            'closed' => ['Ditutup', 'muted', 'lock'],
        ],
        'project' => [
            'active' => ['Berjalan', 'info', 'refresh-cw'],
            'on_hold' => ['Ditunda', 'warning', 'clock'],
            'completed' => ['Selesai', 'success', 'check'],
            'cancelled' => ['Dibatalkan', 'muted', 'circle-dashed'],
        ],
        'milestone' => [
            'pending' => ['Menunggu', 'neutral', 'hourglass'],
            'in_progress' => ['Berjalan', 'info', 'refresh-cw'],
            'completed' => ['Selesai', 'success', 'check'],
            'delayed' => ['Terlambat', 'warning', 'triangle-alert'],
        ],
        'report' => [
            'pending' => ['Menunggu', 'neutral', 'hourglass'],
            'reviewed' => ['Ditinjau', 'info', 'eye'],
            'approved' => ['Disetujui', 'success', 'circle-check'],
            'revision_needed' => ['Perlu Revisi', 'warning', 'circle-alert'],
        ],
        'document' => [
            'pending' => ['Menunggu', 'neutral', 'hourglass'],
            'approved' => ['Disetujui', 'success', 'circle-check'],
            'rejected' => ['Ditolak', 'danger', 'circle-x'],
        ],
        'verification' => [
            'pending_verification' => ['Menunggu Verifikasi', 'neutral', 'hourglass'],
            'verified' => ['Terverifikasi', 'success', 'badge-check'],
            'rejected' => ['Ditolak', 'danger', 'circle-x'],
            'needs_review' => ['Perlu Ditinjau', 'warning', 'circle-alert'],
        ],
        'job_posting' => [
            'draft' => ['Draf', 'neutral', 'pencil'],
            'posted' => ['Dibuka', 'success', 'send'],
            'closed' => ['Ditutup', 'muted', 'lock'],
            'archived' => ['Diarsipkan', 'muted', 'folder-open'],
        ],
        'friend' => [
            'pending' => ['Menunggu', 'neutral', 'hourglass'],
            'accepted' => ['Terhubung', 'success', 'user-check'],
            'rejected' => ['Ditolak', 'danger', 'circle-x'],
            'blocked' => ['Diblokir', 'danger', 'lock'],
        ],
    ];

    /**
     * @return array{label: string, tone: string, icon: string, known: bool}
     */
    public static function resolve(string $domain, ?string $value): array
    {
        $key = strtolower(trim((string) $value));
        $entry = self::MAP[$domain][$key] ?? null;

        if ($entry === null) {
            // nilai tak dikenal: tampilkan apa adanya (tanpa mengarang status), nada netral
            return [
                'label' => $key === '' ? '—' : Str::of($key)->replace('_', ' ')->ucfirst()->toString(),
                'tone' => 'neutral',
                'icon' => 'circle-help',
                'known' => false,
            ];
        }

        return ['label' => $entry[0], 'tone' => $entry[1], 'icon' => $entry[2], 'known' => true];
    }

    public static function label(string $domain, ?string $value): string
    {
        return self::resolve($domain, $value)['label'];
    }

    /** @return list<string> */
    public static function domains(): array
    {
        return array_keys(self::MAP);
    }

    /** @return array<string, array{0: string, 1: string, 2: string}> */
    public static function values(string $domain): array
    {
        return self::MAP[$domain] ?? [];
    }
}
