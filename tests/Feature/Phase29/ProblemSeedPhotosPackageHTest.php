<?php

namespace Tests\Feature\Phase29;

use Database\Seeders\ProblemSeedPhotosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket H: foto seed masalah dari kolam SDG pertama, keterangan netral (D-46).
 * hanya baris seed yang berubah; jumlah baris tetap; lima foto D-42 tidak dipakai.
 */
class ProblemSeedPhotosPackageHTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    // objek bucket yang berisi lima foto D-42 (pemetaan (NN-1) mod 37, fase 8)
    private const WITHHELD_OBJECTS = [6, 7, 13, 16, 18, 43, 44, 50, 53, 55, 80, 81, 87, 90];

    private const WITHHELD_PHOTOS = [
        'central-java-farmer-terraced-fields', 'west-java-elderly-farmers-harvest-rice',
        'central-java-farmer-casting-net-in-paddy', 'west-sumatra-fisherman-net-from-canoe', 'banjarmasin-floating-market',
    ];

    private const OLD_CAPTIONS = [
        'Kondisi lapangan saat ini', 'Situasi yang memerlukan perhatian', 'Area pelaksanaan program',
        'Dokumentasi kondisi eksisting', 'Lokasi kegiatan', 'Gambaran permasalahan', 'Foto survei lokasi',
        'Kondisi infrastruktur', 'Aktivitas masyarakat', 'Potensi yang dapat dikembangkan',
    ];

    /** @var array<int, int> sdg pertama => id masalah */
    private array $problems = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $institutionId = $this->makeInstitution()->id;

        $n = 1;
        foreach ([1, 2, 3, 4, 6, 8, 11, 17] as $i => $sdg) {
            $id = $this->makeOpenProblems($institutionId, 1)[0];
            DB::table('problems')->where('id', $id)->update(['sdg_categories' => json_encode([$sdg, 10])]);
            $this->problems[$sdg] = $id;
            // 4 baris untuk pendidikan dan pasar (kolam 2 foto), selain itu 2-3
            $count = in_array($sdg, [4, 8], true) ? 4 : 2 + $i % 2;
            for ($order = 1; $order <= $count; $order++, $n++) {
                DB::table('problem_images')->insert([
                    'problem_id' => $id, 'image_path' => 'problems/masalah-desa-'.$n.'.jpg',
                    'caption' => self::OLD_CAPTIONS[$n % 10], 'order' => $order, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // unggahan institusi: bukan baris seed, tidak boleh disentuh
        DB::table('problem_images')->insert([
            'problem_id' => $this->problems[2], 'image_path' => 'problems/unggahan-institusi.jpg',
            'caption' => 'Foto survei lokasi', 'order' => 9, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_constants_use_only_32_allowed_photos(): void
    {
        $pooled = array_unique(array_merge(...array_values(ProblemSeedPhotosSeeder::POOLS)));
        sort($pooled);
        $photos = array_keys(ProblemSeedPhotosSeeder::PHOTOS);
        sort($photos);

        $this->assertSame($photos, $pooled);
        $this->assertCount(32, $photos);
        $this->assertSame([], array_intersect($photos, self::WITHHELD_PHOTOS));
        foreach (ProblemSeedPhotosSeeder::PHOTOS as [$path]) {
            $this->assertMatchesRegularExpression(ProblemSeedPhotosSeeder::SEED_PATH, $path);
            $this->assertNotContains((int) preg_replace('/\D/', '', $path), self::WITHHELD_OBJECTS);
        }
    }

    public function test_seed_rows_get_pool_photos_and_neutral_captions(): void
    {
        $before = DB::table('problem_images')->count();
        $this->seed(ProblemSeedPhotosSeeder::class);

        $this->assertSame($before, DB::table('problem_images')->count());
        $allowed = array_column(ProblemSeedPhotosSeeder::PHOTOS, 0);
        $seedRows = DB::table('problem_images')->where('image_path', '!=', 'problems/unggahan-institusi.jpg')->get();

        foreach ($seedRows as $row) {
            $this->assertContains($row->image_path, $allowed);
            $this->assertNotContains($row->caption, self::OLD_CAPTIONS);
            $this->assertMatchesRegularExpression('/^(Gambaran wilayah|Foto ilustrasi(: .+)?)$/', $row->caption);
        }
        foreach ($seedRows->groupBy('problem_id') as $rows) {
            $this->assertSame($rows->count(), $rows->pluck('image_path')->unique()->count(), 'foto ganda dalam satu masalah');
        }
    }

    public function test_sdg_without_matching_photos_uses_region_caption(): void
    {
        $this->seed(ProblemSeedPhotosSeeder::class);

        foreach ([1, 3] as $sdg) {
            $captions = DB::table('problem_images')->where('problem_id', $this->problems[$sdg])->pluck('caption')->unique()->all();
            $this->assertSame([ProblemSeedPhotosSeeder::REGION_CAPTION], $captions);
        }
    }

    public function test_slots_beyond_a_small_pool_fall_back_to_the_region_pool(): void
    {
        $this->seed(ProblemSeedPhotosSeeder::class);

        $rows = DB::table('problem_images')->where('problem_id', $this->problems[4])->orderBy('order')->get();
        $education = array_map(fn ($photo) => ProblemSeedPhotosSeeder::PHOTOS[$photo][0], ProblemSeedPhotosSeeder::POOLS['pendidikan']);

        $this->assertContains($rows[0]->image_path, $education);
        $this->assertContains($rows[1]->image_path, $education);
        $this->assertStringStartsWith('Foto ilustrasi', $rows[0]->caption);
        $this->assertSame(ProblemSeedPhotosSeeder::REGION_CAPTION, $rows[2]->caption);
        $this->assertSame(ProblemSeedPhotosSeeder::REGION_CAPTION, $rows[3]->caption);
    }

    public function test_institution_uploads_are_untouched_and_rerun_is_stable(): void
    {
        $this->seed(ProblemSeedPhotosSeeder::class);
        $first = DB::table('problem_images')->orderBy('id')->get(['id', 'image_path', 'caption'])->toArray();
        $this->seed(ProblemSeedPhotosSeeder::class);

        $this->assertEquals($first, DB::table('problem_images')->orderBy('id')->get(['id', 'image_path', 'caption'])->toArray());
        $upload = DB::table('problem_images')->where('image_path', 'problems/unggahan-institusi.jpg')->first();
        $this->assertSame('Foto survei lokasi', $upload->caption);
    }
}
