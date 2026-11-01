<?php

namespace Tests\Unit;

use App\Support\ProvinceRegion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * phase 29 paket G: pemetaan 38 kode provinsi BPS ke tujuh grup indonesia-regions.svg.
 * database seed hanya punya 5 provinsi (D-14/D-15), jadi semua kode diuji di sini.
 */
class ProvinceRegionTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function provinces(): array
    {
        $rows = [
            ['11', 'Aceh', 'sumatra'], ['12', 'Sumatera Utara', 'sumatra'], ['13', 'Sumatera Barat', 'sumatra'],
            ['14', 'Riau', 'sumatra'], ['15', 'Jambi', 'sumatra'], ['16', 'Sumatera Selatan', 'sumatra'],
            ['17', 'Bengkulu', 'sumatra'], ['18', 'Lampung', 'sumatra'], ['19', 'Kepulauan Bangka Belitung', 'sumatra'],
            ['21', 'Kepulauan Riau', 'sumatra'],
            ['31', 'DKI Jakarta', 'jawa'], ['32', 'Jawa Barat', 'jawa'], ['33', 'Jawa Tengah', 'jawa'],
            ['34', 'DI Yogyakarta', 'jawa'], ['35', 'Jawa Timur', 'jawa'], ['36', 'Banten', 'jawa'],
            ['51', 'Bali', 'nusa-tenggara'], ['52', 'Nusa Tenggara Barat', 'nusa-tenggara'], ['53', 'Nusa Tenggara Timur', 'nusa-tenggara'],
            ['61', 'Kalimantan Barat', 'kalimantan'], ['62', 'Kalimantan Tengah', 'kalimantan'], ['63', 'Kalimantan Selatan', 'kalimantan'],
            ['64', 'Kalimantan Timur', 'kalimantan'], ['65', 'Kalimantan Utara', 'kalimantan'],
            ['71', 'Sulawesi Utara', 'sulawesi'], ['72', 'Sulawesi Tengah', 'sulawesi'], ['73', 'Sulawesi Selatan', 'sulawesi'],
            ['74', 'Sulawesi Tenggara', 'sulawesi'], ['75', 'Gorontalo', 'sulawesi'], ['76', 'Sulawesi Barat', 'sulawesi'],
            ['81', 'Maluku', 'maluku'], ['82', 'Maluku Utara', 'maluku'],
            ['91', 'Papua Barat', 'papua'], ['92', 'Papua Barat Daya', 'papua'], ['94', 'Papua', 'papua'],
            ['95', 'Papua Selatan', 'papua'], ['96', 'Papua Tengah', 'papua'], ['97', 'Papua Pegunungan', 'papua'],
        ];

        $out = [];
        foreach ($rows as [$code, $name, $group]) {
            $out[$code.' '.$name] = [$code, $group];
        }

        return $out;
    }

    #[DataProvider('provinces')]
    public function test_each_bps_province_code_maps_to_its_island_group(string $code, string $group): void
    {
        $this->assertSame($group, ProvinceRegion::group($code));
        // kode kabupaten/kota (4 digit) memakai dua digit pertamanya
        $this->assertSame($group, ProvinceRegion::group($code.'01'));
    }

    public function test_the_table_holds_exactly_the_38_provinces_and_every_group_is_used(): void
    {
        $this->assertCount(38, self::provinces());
        $this->assertSame(array_column(self::provinces(), 1, 0), ProvinceRegion::PROVINCES);
        $this->assertEqualsCanonicalizing(array_keys(ProvinceRegion::GROUPS), array_values(array_unique(ProvinceRegion::PROVINCES)));
    }

    public function test_unknown_or_empty_codes_have_no_group(): void
    {
        foreach ([null, '', ' ', '3', '00', '10', '20', '37', '54', '83', '93', '98', 'xx'] as $code) {
            $this->assertNull(ProvinceRegion::group($code), var_export($code, true));
        }
    }

    public function test_groups_and_labels_match_the_svg_file(): void
    {
        $svg = file_get_contents(dirname(__DIR__, 2).'/public/assets/illustrations/geographic/indonesia-regions.svg');
        preg_match_all('#<g id="region-([a-z-]+)" class="sg-region" data-region="\1"><title>([^<]+)</title>#', $svg, $m);

        $this->assertSame(ProvinceRegion::GROUPS, array_combine($m[1], $m[2]));
    }

    public function test_summary_counts_open_problems_per_group_without_zeros(): void
    {
        $problem = fn (?string $code) => (object) ['province' => $code === null ? null : (object) ['code' => $code]];
        $problems = [$problem('32'), $problem('73'), $problem('33'), $problem('35'), $problem('99'), $problem(null), $problem('74')];

        $summary = ProvinceRegion::summary('34', $problems);

        // tiga di jawa, dua di sulawesi; kode tak dikenal dan provinsi kosong tidak dihitung
        $this->assertSame(['jawa' => 3, 'sulawesi' => 2], $summary['open']);
        $this->assertSame(['jawa', 'sulawesi'], $summary['active']);
        $this->assertSame('jawa', $summary['home']);
        $this->assertNotContains(0, $summary['open']);

        // jumlah sama: urutan mengikuti urutan grup di peta
        $this->assertSame(['sumatra', 'papua'], array_keys(ProvinceRegion::summary(null, [$problem('94'), $problem('11')])['open']));
    }

    public function test_summary_without_open_problems_marks_only_the_home_region(): void
    {
        $this->assertSame(['home' => 'nusa-tenggara', 'open' => [], 'active' => ['nusa-tenggara']], ProvinceRegion::summary('51', []));
        $this->assertSame(['home' => null, 'open' => [], 'active' => []], ProvinceRegion::summary('99', []));
    }
}
