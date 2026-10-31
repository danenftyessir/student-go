<?php

namespace App\Support;

/**
 * titik rujukan geografis untuk peta beranda (phase 4).
 *
 * database hanya menyimpan provinsi dan kabupaten/kota dengan kode BPS, tanpa koordinat.
 * kelas ini memetakan kode BPS ke titik pusat pemerintahan (ibu kota kabupaten/kota atau
 * provinsi), dibulatkan sampai sekitar satu kilometer. ini fakta geografis, bukan data produk:
 * jumlah masalah tetap dihitung dari database.
 *
 * kode yang tidak dikenal mengembalikan null; pemanggil lalu memakai titik provinsi,
 * atau tidak menaruh pin sama sekali (angka di daftar tetap tampil).
 */
class RegionCentroids
{
    /** kabupaten/kota yang ada di data seed (kode BPS 4 digit) => [lat, lng] */
    private const REGENCIES = [
        '3171' => [-6.2615, 106.8106], // kota jakarta selatan
        '3172' => [-6.2250, 106.9004], // kota jakarta timur
        '3173' => [-6.1865, 106.8341], // kota jakarta pusat
        '3174' => [-6.1674, 106.7637], // kota jakarta barat
        '3175' => [-6.1384, 106.8827], // kota jakarta utara
        '3201' => [-6.4817, 106.8540], // kab. bogor (cibinong)
        '3204' => [-7.0253, 107.5197], // kab. bandung (soreang)
        '3273' => [-6.9175, 107.6191], // kota bandung
        '3275' => [-6.2383, 106.9756], // kota bekasi
        '3276' => [-6.4025, 106.7942], // kota depok
        '3301' => [-7.7183, 109.0154], // kab. cilacap
        '3310' => [-7.7056, 110.6064], // kab. klaten
        '3372' => [-7.5755, 110.8243], // kota surakarta
        '3374' => [-6.9667, 110.4167], // kota semarang
        '3401' => [-7.8572, 110.1590], // kab. kulon progo (wates)
        '3402' => [-7.8881, 110.3289], // kab. bantul
        '3403' => [-7.9655, 110.6007], // kab. gunung kidul (wonosari)
        '3404' => [-7.7164, 110.3556], // kab. sleman
        '3471' => [-7.7956, 110.3695], // kota yogyakarta
        '3501' => [-8.1945, 111.1055], // kab. pacitan
        '3578' => [-7.2575, 112.7521], // kota surabaya
        '3579' => [-7.9666, 112.6326], // kota malang
    ];

    /** ibu kota provinsi (kode BPS 2 digit) => [lat, lng]. kode provinsi papua hasil pemekaran sengaja tidak diisi. */
    private const PROVINCES = [
        '11' => [5.5483, 95.3238],    // aceh, banda aceh
        '12' => [3.5952, 98.6722],    // sumatera utara, medan
        '13' => [-0.9471, 100.4172],  // sumatera barat, padang
        '14' => [0.5071, 101.4478],   // riau, pekanbaru
        '15' => [-1.6101, 103.6131],  // jambi
        '16' => [-2.9761, 104.7754],  // sumatera selatan, palembang
        '17' => [-3.7928, 102.2608],  // bengkulu
        '18' => [-5.3971, 105.2668],  // lampung, bandar lampung
        '19' => [-2.1291, 106.1090],  // kepulauan bangka belitung, pangkalpinang
        '21' => [0.9186, 104.4554],   // kepulauan riau, tanjungpinang
        '31' => [-6.2088, 106.8456],  // dki jakarta
        '32' => [-6.9175, 107.6191],  // jawa barat, bandung
        '33' => [-6.9667, 110.4167],  // jawa tengah, semarang
        '34' => [-7.7956, 110.3695],  // di yogyakarta
        '35' => [-7.2575, 112.7521],  // jawa timur, surabaya
        '36' => [-6.1200, 106.1503],  // banten, serang
        '51' => [-8.6705, 115.2126],  // bali, denpasar
        '52' => [-8.5833, 116.1167],  // nusa tenggara barat, mataram
        '53' => [-10.1772, 123.6070], // nusa tenggara timur, kupang
        '61' => [-0.0263, 109.3425],  // kalimantan barat, pontianak
        '62' => [-2.2161, 113.9135],  // kalimantan tengah, palangka raya
        '63' => [-3.4572, 114.8103],  // kalimantan selatan, banjarbaru
        '64' => [-0.5022, 117.1536],  // kalimantan timur, samarinda
        '65' => [2.8375, 117.3653],   // kalimantan utara, tanjung selor
        '71' => [1.4748, 124.8421],   // sulawesi utara, manado
        '72' => [-0.8917, 119.8707],  // sulawesi tengah, palu
        '73' => [-5.1477, 119.4327],  // sulawesi selatan, makassar
        '74' => [-3.9985, 122.5130],  // sulawesi tenggara, kendari
        '75' => [0.5435, 123.0568],   // gorontalo
        '76' => [-2.6748, 118.8885],  // sulawesi barat, mamuju
        '81' => [-3.6954, 128.1814],  // maluku, ambon
        '82' => [0.7370, 127.5590],   // maluku utara, sofifi
    ];

    /** digit pertama kode provinsi BPS => grup pulau pada indonesia-regions.svg */
    private const ISLAND_GROUPS = [
        '1' => 'sumatra',
        '3' => 'jawa',
        '5' => 'nusa-tenggara',
        '6' => 'kalimantan',
        '7' => 'sulawesi',
        '8' => 'maluku',
        '9' => 'papua',
    ];

    /**
     * titik untuk kabupaten/kota; jatuh ke ibu kota provinsi bila kodenya belum terdaftar.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function forRegency(?string $regencyCode, ?string $provinceCode = null): ?array
    {
        if ($regencyCode !== null && isset(self::REGENCIES[$regencyCode])) {
            return self::REGENCIES[$regencyCode];
        }

        return self::forProvince($provinceCode ?? ($regencyCode ? substr($regencyCode, 0, 2) : null));
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    public static function forProvince(?string $provinceCode): ?array
    {
        return $provinceCode !== null ? (self::PROVINCES[$provinceCode] ?? null) : null;
    }

    /** grup pulau (id region-* di indonesia-regions.svg) untuk kode provinsi */
    public static function islandGroup(?string $provinceCode): ?string
    {
        return $provinceCode ? (self::ISLAND_GROUPS[substr($provinceCode, 0, 1)] ?? null) : null;
    }
}
