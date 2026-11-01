<?php

namespace Tests\Feature\Phase29;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket G (D-45): peta wilayah di profil publik instansi. svg inline dari berkas yang tidak diubah,
 * grup aktif .is-active, padanan teks dari query nyata ('3 masalah dibuka di Jawa'), tanpa angka 0,
 * dan tanpa data publik baru (hanya provinsi dan jumlah masalah dibuka).
 */
class InstitutionRegionMapPackageGTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private const SVG = 'assets/illustrations/geographic/indonesia-regions.svg';

    private const SVG_SHA256 = 'd2debbc81fd712bdc2af4d51f40a06cc239c92149b7e81f1429a14494f0c51ad';

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-01 09:00:00');
        $this->seedRegion();
    }

    private function text(string $html): string
    {
        return preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html)));
    }

    /** potongan html bagian wilayah, atau null bila tidak ada */
    private function regionSection(string $html): ?string
    {
        return preg_match('#<section class="sg-org__section sg-region-map".*?</section>#s', $html, $m) ? $m[0] : null;
    }

    /** padanan teks: daftar di bawah peta (teks di dalam svg tersembunyi dari pembaca layar) */
    private function regionText(string $html): string
    {
        preg_match('#<ul class="sg-region-map__list">.*?</ul>#s', $html, $m);

        return trim($this->text($m[0] ?? ''));
    }

    private function activeGroups(string $html): array
    {
        preg_match_all('#<g id="region-([a-z-]+)" class="sg-region is-active"#', $html, $m);

        return $m[1];
    }

    private function province(string $name, string $code): int
    {
        return DB::table('provinces')->insertGetId(['name' => $name, 'code' => $code]);
    }

    public function test_open_problems_are_counted_per_island_group_from_the_database(): void
    {
        $institution = $this->makeInstitution();
        $this->makeOpenProblems($institution->id, 3);
        $more = $this->makeOpenProblems($institution->id, 3);
        // satu di sulawesi selatan, satu selesai di bali (tidak dihitung), satu draft (tidak publik)
        DB::table('problems')->where('id', $more[0])->update(['province_id' => $this->province('Sulawesi Selatan', '73')]);
        DB::table('problems')->where('id', $more[1])->update(['province_id' => $this->province('Bali', '51'), 'status' => 'completed']);
        DB::table('problems')->where('id', $more[2])->update(['status' => 'draft']);

        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $this->assertNotNull($this->regionSection($html));
        $text = $this->regionText($html);

        $this->assertStringContainsString('3 masalah dibuka di Jawa', $text);
        $this->assertStringContainsString('1 masalah dibuka di Sulawesi', $text);
        $this->assertStringContainsString('Wilayah asal instansi: Jawa', $text);
        $this->assertStringNotContainsString('Bali dan Nusa Tenggara', $text);
        $this->assertSame(['jawa', 'sulawesi'], $this->activeGroups($html));

        // jumlah di teks sama dengan query masalah dibuka (dan dengan ringkasan di kepala halaman)
        preg_match_all('/(\d+) masalah dibuka di /', $text, $m);
        $this->assertSame($institution->problems()->where('status', 'open')->count(), array_sum(array_map('intval', $m[1])));
    }

    public function test_without_open_problems_only_the_home_region_is_shown_and_no_zero(): void
    {
        $institution = $this->makeInstitution();
        $ids = $this->makeOpenProblems($institution->id, 1);
        DB::table('problems')->where('id', $ids[0])->update(['status' => 'completed']);

        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $text = $this->regionText($html);

        $this->assertStringContainsString('Wilayah asal instansi: Jawa', $text);
        $this->assertStringNotContainsString('masalah dibuka di', $text);
        $this->assertDoesNotMatchRegularExpression('/\b0 masalah/', $this->text($html));
        $this->assertSame(['jawa'], $this->activeGroups($html));
    }

    public function test_unknown_home_province_without_open_problems_hides_the_map(): void
    {
        $institution = $this->makeInstitution();
        $institution->update(['province_id' => $this->province('Provinsi Uji', '99')]);

        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();

        $this->assertNull($this->regionSection($html));
        $this->assertStringNotContainsString('region-jawa', $html);
    }

    public function test_svg_is_inlined_from_the_unchanged_file_and_hidden_from_screen_readers(): void
    {
        $institution = $this->makeInstitution();
        $this->makeOpenProblems($institution->id, 2);

        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $section = $this->regionSection($html);

        $this->assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 390" class="sg-region-map__svg" aria-hidden="true" focusable="false">', $section);
        $this->assertSame(7, preg_match_all('#<g id="region-[a-z-]+" class="sg-region( is-active)?"#', $section));
        // <style> dan id 't' bawaan svg tidak ikut ke halaman; tidak ada <img> atau tautan ke berkas
        $this->assertStringNotContainsString('<style', $section);
        $this->assertStringNotContainsString('id="t"', $html);
        $this->assertStringNotContainsString(self::SVG, $html);
        $this->assertSame(1, substr_count($html, '<h1'));

        $this->assertSame(self::SVG_SHA256, hash_file('sha256', public_path(self::SVG)));
    }

    public function test_page_shows_no_new_public_data(): void
    {
        $institution = $this->makeInstitution();
        $this->makeOpenProblems($institution->id, 1);

        $html = $this->get('/institution/'.$institution->id)->assertOk()->getContent();
        $text = $this->regionText($html);

        // hanya grup pulau dan jumlah masalah dibuka; alamat, email dan telepon tidak tampil
        $this->assertSame('1 masalah dibuka di Jawa Wilayah asal instansi: Jawa', $text);
        foreach ([$institution->address, $institution->email, $institution->phone] as $private) {
            $this->assertStringNotContainsString($private, $html);
        }
    }

    public function test_changed_views_have_no_style_block_or_hex_colour(): void
    {
        foreach (['institution/profile/public.blade.php', 'institution/profile/components/region-map.blade.php'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));
            $this->assertStringNotContainsString('<style', $source, $view);
            $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $source, $view);
        }
    }
}
