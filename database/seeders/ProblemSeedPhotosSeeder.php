<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * seeder perbaikan foto seed masalah (phase 29 paket H, D-46)
 * memasangkan foto katalog ke masalah menurut SDG pertamanya, dengan keterangan netral.
 *
 * - hanya mengubah image_path dan caption pada baris seed (problems/masalah-desa-NN.jpg|jpeg)
 * - tidak menambah atau menghapus baris, tidak menulis ke supabase storage
 * - objek di bucket sg-storage sudah ada (fase 8): foto ke-((NN-1) mod 37) di photo-review.json
 * - hanya 32 foto: 37 foto 'available' dikurangi lima foto yang ditahan D-42
 *
 * jalankan: php artisan db:seed --class=ProblemSeedPhotosSeeder
 */
class ProblemSeedPhotosSeeder extends Seeder
{
    // pola path baris seed; unggahan institusi tidak pernah cocok
    public const SEED_PATH = '/^problems\/masalah-desa-\d+\.(jpg|jpeg)$/';

    public const REGION_CAPTION = 'Gambaran wilayah';

    // foto => [objek di bucket, lokasi asli foto dari credits.json atau null]
    public const PHOTOS = [
        'purwojati-banyumas-rice-fields' => ['problems/masalah-desa-1.jpg', 'Purwojati, Banyumas'],
        'flooded-rice-paddies-farmers-aerial' => ['problems/masalah-desa-2.jpg', null],
        'jatiluwih-bali-rice-planting' => ['problems/masalah-desa-3.jpeg', 'Jatiluwih, Tabanan, Bali'],
        'aceh-rice-field-farmer' => ['problems/masalah-desa-4.jpg', 'Aceh'],
        'tegalwaru-west-java-aerial-rice-fields' => ['problems/masalah-desa-5.jpeg', 'Tegalwaru, Jawa Barat'],
        'yogyakarta-farmer-planting-rice' => ['problems/masalah-desa-8.jpeg', 'Yogyakarta'],
        'bali-aerial-tractor-plowing-rice-field' => ['problems/masalah-desa-9.jpg', 'Bali'],
        'west-java-drone-cropland-near-forest' => ['problems/masalah-desa-10.jpg', 'Jawa Barat'],
        'bali-rice-terraces-lone-farmer' => ['problems/masalah-desa-11.jpg', 'Bali'],
        'bengkulu-aerial-rice-terraces-sunset' => ['problems/masalah-desa-12.jpg', 'Bengkulu'],
        'phinisi-boats-in-harbor' => ['problems/masalah-desa-14.jpg', null],
        'bangka-belitung-fisherman-casting-net-sunrise' => ['problems/masalah-desa-15.jpg', 'Kepulauan Bangka Belitung'],
        'ntb-fish-market' => ['problems/masalah-desa-17.jpeg', 'Nusa Tenggara Barat'],
        'maluku-islands-aerial' => ['problems/masalah-desa-19.jpeg', 'Kepulauan Maluku'],
        'north-maluku-boats-on-beach-aerial' => ['problems/masalah-desa-20.jpeg', 'Maluku Utara'],
        'waigeo-barat-coastal-huts-aerial' => ['problems/masalah-desa-21.jpeg', 'Waigeo Barat'],
        'papua-island-landscape' => ['problems/masalah-desa-22.jpeg', 'Papua'],
        'southeast-sulawesi-stilt-houses-by-river' => ['problems/masalah-desa-23.jpeg', 'Sulawesi Tenggara'],
        'cirebon-aerial-village-red-roofs' => ['problems/masalah-desa-24.jpg', 'Ciledug, Cirebon'],
        'penglipuran-village-path' => ['problems/masalah-desa-25.jpeg', 'Desa Penglipuran, Bangli, Bali'],
        'rural-road-house-and-mountain' => ['problems/masalah-desa-26.jpeg', null],
        'kutai-kartanegara-aerial-town-and-river' => ['problems/masalah-desa-27.jpeg', 'Loa Kulu, Kutai Kartanegara'],
        'sidemen-bali-rice-terraces-at-dawn' => ['problems/masalah-desa-28.jpg', 'Sidemen, Bali'],
        'bali-woman-working-in-rice-field' => ['problems/masalah-desa-29.jpg', 'Bali'],
        'universitas-indonesia-graduation-flags' => ['problems/masalah-desa-30.jpeg', 'Universitas Indonesia, Depok'],
        'surakarta-market-shoppers' => ['problems/masalah-desa-31.jpg', 'Surakarta'],
        'umy-graduates-in-red-gowns' => ['problems/masalah-desa-32.jpg', 'Universitas Muhammadiyah Yogyakarta, Bantul'],
        'gili-gede-mangroves-on-shore' => ['problems/masalah-desa-33.jpg', 'Gili Gede, Nusa Tenggara Barat'],
        'batam-beach-litter-in-tree-roots' => ['problems/masalah-desa-34.jpg', 'Pantai Viovio, Batam'],
        'bontang-wooden-dock-and-mangroves' => ['problems/masalah-desa-35.jpg', 'Bontang, Kalimantan Timur'],
        'komodo-boats-on-water' => ['problems/masalah-desa-36.jpg', 'Pulau Komodo, Manggarai Barat'],
        'kebumen-three-cyclists-across-field' => ['problems/masalah-desa-37.jpg', 'Kebumen, Jawa Tengah'],
    ];

    // kolam foto (audit U2); urutan menentukan rotasi
    public const POOLS = [
        'pertanian' => [
            'purwojati-banyumas-rice-fields', 'flooded-rice-paddies-farmers-aerial', 'bengkulu-aerial-rice-terraces-sunset',
            'jatiluwih-bali-rice-planting', 'aceh-rice-field-farmer', 'tegalwaru-west-java-aerial-rice-fields',
            'yogyakarta-farmer-planting-rice', 'bali-aerial-tractor-plowing-rice-field', 'west-java-drone-cropland-near-forest',
            'bali-rice-terraces-lone-farmer', 'sidemen-bali-rice-terraces-at-dawn', 'bali-woman-working-in-rice-field',
        ],
        'pesisir' => [
            'phinisi-boats-in-harbor', 'bangka-belitung-fisherman-casting-net-sunrise', 'north-maluku-boats-on-beach-aerial',
            'gili-gede-mangroves-on-shore', 'batam-beach-litter-in-tree-roots', 'bontang-wooden-dock-and-mangroves',
            'komodo-boats-on-water', 'maluku-islands-aerial', 'waigeo-barat-coastal-huts-aerial', 'papua-island-landscape',
        ],
        'pasar' => ['ntb-fish-market', 'surakarta-market-shoppers'],
        'pendidikan' => ['universitas-indonesia-graduation-flags', 'umy-graduates-in-red-gowns'],
        'desa' => [
            'southeast-sulawesi-stilt-houses-by-river', 'penglipuran-village-path', 'rural-road-house-and-mountain',
            'kebumen-three-cyclists-across-field', 'cirebon-aerial-village-red-roofs', 'kutai-kartanegara-aerial-town-and-river',
        ],
        // kolam netral: desa ditambah tiga lanskap tanpa aktivitas
        'wilayah' => [
            'southeast-sulawesi-stilt-houses-by-river', 'penglipuran-village-path', 'rural-road-house-and-mountain',
            'kebumen-three-cyclists-across-field', 'cirebon-aerial-village-red-roofs', 'kutai-kartanegara-aerial-town-and-river',
            'maluku-islands-aerial', 'papua-island-landscape', 'waigeo-barat-coastal-huts-aerial',
        ],
    ];

    // SDG pertama => kolam; SDG 1, 3, 5, 7 tanpa foto yang sepadan memakai kolam wilayah
    public const SDG_POOL = [
        1 => 'wilayah', 2 => 'pertanian', 3 => 'wilayah', 4 => 'pendidikan', 5 => 'wilayah', 6 => 'pesisir',
        7 => 'wilayah', 8 => 'pasar', 9 => 'desa', 10 => 'desa', 11 => 'desa', 12 => 'pasar',
        13 => 'pesisir', 14 => 'pesisir', 15 => 'pertanian', 16 => 'desa', 17 => 'desa',
    ];

    /**
     * hitung rencana tanpa menulis apa pun: [id baris => ['image_path' => ..., 'caption' => ...]]
     */
    public function plan(): array
    {
        $rows = DB::table('problem_images')->orderBy('problem_id')->orderBy('order')->orderBy('id')->get()
            ->filter(fn ($row) => preg_match(self::SEED_PATH, (string) $row->image_path))
            ->groupBy('problem_id');
        $sdgs = DB::table('problems')->whereIn('id', $rows->keys())->pluck('sdg_categories', 'id');

        $use = [];
        $cursor = [];
        $plan = [];

        foreach ($rows as $problemId => $images) {
            $categories = json_decode((string) $sdgs[$problemId], true) ?: [];
            $pool = self::SDG_POOL[(int) ($categories[0] ?? 0)] ?? 'wilayah';
            $chosen = [];

            foreach ($images as $image) {
                // slot melebihi isi kolam diisi dari kolam wilayah
                $source = count($chosen) < count(self::POOLS[$pool]) ? $pool : 'wilayah';
                $list = self::POOLS[$source];
                $size = count($list);
                $start = $cursor[$source] ?? 0;

                // foto dengan pemakaian terendah, mulai dari kursor kolam; tidak ada foto ganda dalam satu masalah
                $candidates = array_values(array_diff($list, $chosen));
                usort($candidates, function ($a, $b) use ($use, $list, $size, $start) {
                    return [$use[$a] ?? 0, ((array_search($a, $list) - $start) % $size + $size) % $size]
                        <=> [$use[$b] ?? 0, ((array_search($b, $list) - $start) % $size + $size) % $size];
                });
                $photo = $candidates[0];

                $chosen[] = $photo;
                $use[$photo] = ($use[$photo] ?? 0) + 1;
                $cursor[$source] = (array_search($photo, $list) + 1) % $size;

                [$path, $location] = self::PHOTOS[$photo];
                $neutral = $pool === 'wilayah' || $source !== $pool;
                $plan[$image->id] = [
                    'image_path' => $path,
                    'caption' => $neutral ? self::REGION_CAPTION : ($location ? 'Foto ilustrasi: '.$location : 'Foto ilustrasi'),
                ];
            }
        }

        return $plan;
    }

    public function run(): void
    {
        $plan = $this->plan();

        DB::transaction(function () use ($plan) {
            foreach ($plan as $id => $values) {
                DB::table('problem_images')->where('id', $id)->update($values + ['updated_at' => now()]);
            }
        });

        $this->command?->info(count($plan).' baris foto seed masalah diperbarui.');
    }
}
