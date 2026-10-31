<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * label yang mudah dibaca untuk metadata repositori (phase 14): tipe file tanpa MIME mentah,
 * ukuran file dengan koma desimal, dan SDG sebagai nomor + nama dari sdg_label() (daftar yang sama dengan phase 22).
 */
class RepositoryFormat
{
    // MIME atau ekstensi tersimpan -> label pendek
    private const FILE_TYPES = [
        'pdf' => 'PDF',
        'application/pdf' => 'PDF',
        'doc' => 'DOC',
        'application/msword' => 'DOC',
        'docx' => 'DOCX',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
        'xls' => 'XLS',
        'application/vnd.ms-excel' => 'XLS',
        'xlsx' => 'XLSX',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
        'ppt' => 'PPT',
        'application/vnd.ms-powerpoint' => 'PPT',
        'pptx' => 'PPTX',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PPTX',
    ];

    public static function fileType(?string $type): ?string
    {
        $type = strtolower(trim((string) $type));
        if ($type === '') {
            return null;
        }
        if (isset(self::FILE_TYPES[$type])) {
            return self::FILE_TYPES[$type];
        }

        // tipe lain: ambil bagian setelah '/', tanpa menampilkan MIME utuh
        $short = str_contains($type, '/') ? substr($type, strrpos($type, '/') + 1) : $type;

        return preg_match('/^[a-z0-9]{1,8}$/', $short) ? strtoupper($short) : 'Berkas';
    }

    public static function fileSize(?int $bytes): ?string
    {
        if (! $bytes || $bytes < 0) {
            return null;
        }
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        // mulai 1.000 KB tampil sebagai MB (hindari "1.000 KB")
        $kb = (int) round($bytes / 1024);
        if ($kb < 1000) {
            return $kb.' KB';
        }

        return number_format($bytes / (1024 * 1024), 1, ',', '.').' MB';
    }

    // "PDF · 1,4 MB"
    public static function fileChip(?string $type, ?int $bytes): ?string
    {
        $parts = array_filter([self::fileType($type), self::fileSize($bytes)]);

        return $parts ? implode(' · ', $parts) : null;
    }

    /**
     * kategori dokumen (array angka 1-17, atau string JSON) -> [['id' => 4, 'name' => 'Pendidikan Berkualitas'], …]
     */
    public static function sdgs($categories): array
    {
        if (is_string($categories)) {
            $categories = json_decode($categories, true) ?? [];
        }

        return collect((array) $categories)
            ->map(fn ($c) => (int) $c)
            ->filter(fn ($c) => $c >= 1 && $c <= 17)
            ->unique()
            ->values()
            ->map(fn ($c) => ['id' => $c, 'name' => sdg_label($c)])
            ->all();
    }

    /**
     * dokumen terkait tanpa duplikat: satu entri per judul (data memuat judul kembar), tidak sama dengan dokumen yang dibuka
     */
    public static function uniqueRelated(Collection $documents, ?string $currentTitle, int $limit = 4): Collection
    {
        $key = fn ($title) => mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $title)));
        $current = $key($currentTitle);

        return $documents
            ->reject(fn ($doc) => $key($doc->title) === $current)
            ->unique(fn ($doc) => $key($doc->title))
            ->take($limit)
            ->values();
    }

    public static function number($value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
