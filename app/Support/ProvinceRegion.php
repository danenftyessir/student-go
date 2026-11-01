<?php

namespace App\Support;

/**
 * kelompok pulau untuk peta wilayah di profil publik instansi (phase 29, paket G, D-45).
 *
 * dua digit pertama kode provinsi BPS dipetakan ke tujuh grup <g id="region-*"> pada
 * public/assets/illustrations/geographic/indonesia-regions.svg. ini fakta geografis, bukan data
 * produk: jumlah masalah tetap dihitung dari database. kode yang tidak dikenal mengembalikan null.
 */
class ProvinceRegion
{
    /** grup => label, urutan dan label sama dengan <title> di berkas svg */
    public const GROUPS = [
        'sumatra' => 'Sumatra',
        'jawa' => 'Jawa',
        'kalimantan' => 'Kalimantan',
        'sulawesi' => 'Sulawesi',
        'nusa-tenggara' => 'Bali dan Nusa Tenggara',
        'maluku' => 'Kepulauan Maluku',
        'papua' => 'Papua',
    ];

    /** 38 kode provinsi BPS (dua digit) => grup */
    public const PROVINCES = [
        '11' => 'sumatra',       // aceh
        '12' => 'sumatra',       // sumatera utara
        '13' => 'sumatra',       // sumatera barat
        '14' => 'sumatra',       // riau
        '15' => 'sumatra',       // jambi
        '16' => 'sumatra',       // sumatera selatan
        '17' => 'sumatra',       // bengkulu
        '18' => 'sumatra',       // lampung
        '19' => 'sumatra',       // kepulauan bangka belitung
        '21' => 'sumatra',       // kepulauan riau
        '31' => 'jawa',          // dki jakarta
        '32' => 'jawa',          // jawa barat
        '33' => 'jawa',          // jawa tengah
        '34' => 'jawa',          // di yogyakarta
        '35' => 'jawa',          // jawa timur
        '36' => 'jawa',          // banten
        '51' => 'nusa-tenggara', // bali
        '52' => 'nusa-tenggara', // nusa tenggara barat
        '53' => 'nusa-tenggara', // nusa tenggara timur
        '61' => 'kalimantan',    // kalimantan barat
        '62' => 'kalimantan',    // kalimantan tengah
        '63' => 'kalimantan',    // kalimantan selatan
        '64' => 'kalimantan',    // kalimantan timur
        '65' => 'kalimantan',    // kalimantan utara
        '71' => 'sulawesi',      // sulawesi utara
        '72' => 'sulawesi',      // sulawesi tengah
        '73' => 'sulawesi',      // sulawesi selatan
        '74' => 'sulawesi',      // sulawesi tenggara
        '75' => 'sulawesi',      // gorontalo
        '76' => 'sulawesi',      // sulawesi barat
        '81' => 'maluku',        // maluku
        '82' => 'maluku',        // maluku utara
        '91' => 'papua',         // papua barat
        '92' => 'papua',         // papua barat daya
        '94' => 'papua',         // papua
        '95' => 'papua',         // papua selatan
        '96' => 'papua',         // papua tengah
        '97' => 'papua',         // papua pegunungan
    ];

    private const SVG_PATH = 'assets/illustrations/geographic/indonesia-regions.svg';

    /** grup untuk kode provinsi (atau kode kabupaten/kota, cukup dua digit pertamanya) */
    public static function group(?string $code): ?string
    {
        $code = trim((string) $code);

        return strlen($code) >= 2 ? (self::PROVINCES[substr($code, 0, 2)] ?? null) : null;
    }

    public static function label(string $group): string
    {
        return self::GROUPS[$group] ?? $group;
    }

    /**
     * ringkasan untuk peta: grup asal instansi dan jumlah masalah dibuka per grup.
     * hanya grup dengan minimal satu masalah yang masuk ke 'open' (tidak pernah ada angka 0).
     *
     * @param  iterable<\App\Models\Problem>  $openProblems  masalah berstatus open yang sudah dimuat bersama provinsinya
     * @return array{home: ?string, open: array<string, int>, active: list<string>}
     */
    public static function summary(?string $homeProvinceCode, iterable $openProblems): array
    {
        $open = [];
        foreach ($openProblems as $problem) {
            $group = self::group($problem->province?->code);
            if ($group !== null) {
                $open[$group] = ($open[$group] ?? 0) + 1;
            }
        }

        // urut menurut jumlah, lalu menurut urutan grup di peta
        $order = array_flip(array_keys(self::GROUPS));
        uksort($open, fn ($a, $b) => [$open[$b], $order[$a]] <=> [$open[$a], $order[$b]]);

        $home = self::group($homeProvinceCode);
        $active = $open ? array_keys($open) : array_filter([$home]);

        return ['home' => $home, 'open' => $open, 'active' => array_values($active)];
    }

    /**
     * isi svg untuk disisipkan inline. berkas aslinya tidak diubah; di sini hanya: blok <style>
     * dan <title> akar dibuang (warna datang dari token di public-pages.css, id 't' tidak bentrok),
     * svg disembunyikan dari pembaca layar (padanan teksnya ada di halaman), dan grup aktif diberi .is-active.
     *
     * @param  list<string>  $active
     */
    public static function inlineSvg(array $active = []): string
    {
        static $source = null;
        $source ??= (string) @file_get_contents(public_path(self::SVG_PATH));
        if ($source === '') {
            return '';
        }

        $svg = preg_replace('#<style>.*?</style>#s', '', $source, 1);
        $svg = preg_replace('#<title id="t">.*?</title>#s', '', $svg, 1);
        $svg = str_replace('role="img" aria-labelledby="t"', 'class="sg-region-map__svg" aria-hidden="true" focusable="false"', $svg);

        foreach ($active as $group) {
            if (isset(self::GROUPS[$group])) {
                $svg = str_replace('class="sg-region" data-region="'.$group.'"', 'class="sg-region is-active" data-region="'.$group.'"', $svg);
            }
        }

        return $svg;
    }
}
