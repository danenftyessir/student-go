<?php

namespace Tests\Feature\Phase4;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 4 (revisi): beranda. semua angka dari query nyata (J9), tanpa nama/tagline karsa (RN-01, RN-02),
 * tanpa aos/merriweather/lora, daftar masalah dengan informasi pembeda, peta leaflet dengan data nyata,
 * foto lapangan asli (D-30), dan halaman tetap tampil tanpa blok data bila database gagal.
 * pembuka video (AD-11) diuji terpisah oleh HeroVideoTest.
 */
class HomepageTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-31 09:00:00');
        $this->seedRegion();
    }

    private function text(string $html): string
    {
        return preg_replace('/\s+/', ' ', strip_tags($html));
    }

    public function test_homepage_has_student_go_identity_and_no_karsa_content(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="id"', $html);
        $this->assertStringContainsString('<title>Platform Digital untuk Kuliah Kerja Nyata — Student Go</title>', $html);
        $this->assertSame(1, substr_count($html, '<h1'));

        // RN-01 dan RN-02: bagian fitur generik dan tagline karsa hilang, tanpa tagline pengganti
        $this->assertStringNotContainsString('Kenapa Memilih', $html);
        $this->assertStringNotContainsString('Karya Untuk Bangsa', $html);
        $this->assertStringNotContainsString('Tetaplah bergerak', $html);
        $this->assertStringNotContainsStringIgnoringCase('karsa', $this->text($html));
    }

    public function test_homepage_drops_aos_old_fonts_and_hard_coded_metrics(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['data-aos', 'aos.js', 'aos.css', 'Merriweather', 'family=Lora', 'hero-background.jpg'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, $needle);
        }
        $text = $this->text($html);
        foreach (['34 Provinsi', '514', '85+', '120+', 'ribuan mahasiswa'] as $needle) {
            $this->assertStringNotContainsString($needle, $text, $needle);
        }
    }

    public function test_problem_rows_show_differentiating_facts_from_the_database(): void
    {
        $institution = $this->makeInstitution();
        $ids = $this->makeOpenProblems($institution->id, 7);
        DB::table('problems')->where('id', $ids[0])->update([
            'title' => 'Pemetaan Air Bersih Dusun', 'required_students' => 4, 'duration_months' => 2,
            'difficulty_level' => 'advanced', 'is_urgent' => true, 'sdg_categories' => json_encode([6, 11]),
            'application_deadline' => '2026-11-10',
        ]);
        $closed = $this->makeOpenProblems($institution->id, 2);
        DB::table('problems')->whereIn('id', $closed)->update(['status' => 'closed']);

        $html = $this->get('/')->assertOk()->getContent();
        $text = $this->text($html);

        // ringkasan hanya menghitung masalah yang dibuka
        $this->assertStringContainsString('7 masalah dibuka', $text);
        $this->assertStringContainsString('1 instansi', $text);
        $this->assertStringContainsString('Lihat semua 7 masalah', $text);

        // tenggat terdekat lebih dulu; lima baris; informasi pembeda tampil
        $this->assertSame(5, substr_count($html, 'class="sg-home-problem"'));
        $first = strpos($html, 'Pemetaan Air Bersih Dusun');
        $this->assertNotFalse($first);
        $this->assertLessThan(strpos($html, 'Masalah 2'), $first);
        $this->assertStringContainsString('4 mahasiswa', $text);
        $this->assertStringContainsString('2 bulan', $text);
        $this->assertStringContainsString('Lanjut', $text);
        $this->assertStringContainsString('Mendesak', $text);
        $this->assertStringContainsString('SDG 6 · Air bersih dan sanitasi', $text);
        $this->assertStringContainsString('Tutup dalam 10 hari', $text);
        $this->assertStringContainsString('Tenggat 10 Nov 2026', $text);
        $this->assertStringContainsString(route('student.browse-problems.show', $ids[0]), $html);
        $this->assertStringContainsString(route('institution.public', $institution->id), $html);

        // bidang sdg dihitung dari masalah dibuka; 'mendesak' hanya bila ditandai
        $this->assertStringContainsString('Bidang yang paling banyak dibutuhkan', $text);
        $this->assertStringContainsString('1 masalah ditandai mendesak', $text);
    }

    public function test_map_uses_real_points_and_problems(): void
    {
        $institution = $this->makeInstitution();
        $this->makeOpenProblems($institution->id, 3);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('leaflet@1.9.4/dist/leaflet.js', $html);
        $this->assertStringContainsString('id="map"', $html);
        $this->assertStringContainsString('"name":"Kota Bandung","province":"Jawa Barat"', $html);
        $this->assertSame(3, substr_count($html, '"point":"'.$this->regencyId.'"'));
        $this->assertStringContainsString('assets\/illustrations\/motifs\/map-pin.svg', $html);
        $this->assertStringContainsString('id="map-province"', $html);
        $this->assertStringContainsString('id="map-sdg"', $html);
    }

    public function test_outcomes_come_from_the_projects_table(): void
    {
        $student = $this->makeStudent('Sari', 'Wulandari');
        $institution = $this->makeInstitution();
        [$problemId] = $this->makeOpenProblems($institution->id, 1);
        $this->makeProject($student, $institution->id, $problemId);

        $text = $this->text($this->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('Proyek KKN di Student Go sejauh ini', $text);
        $this->assertStringContainsString('mahasiswa pernah terlibat proyek', $text);
    }

    public function test_empty_database_shows_no_zero_counters_and_no_map(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $text = $this->text($html);

        $this->assertStringNotContainsString('0 masalah dibuka', $text);
        $this->assertStringNotContainsString('id="map"', $html);
        $this->assertStringNotContainsString('Proyek KKN di Student Go sejauh ini', $text);
        $this->assertStringContainsString('Belum ada masalah yang dibuka', $text);
    }

    public function test_database_failure_hides_data_blocks_instead_of_erroring(): void
    {
        Schema::drop('problems');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('masalah dibuka', $this->text($html));
        $this->assertStringNotContainsString('id="map"', $html);
        $this->assertStringContainsString('Dari memilih masalah sampai portofolio', $html);
        $this->assertStringContainsString(route('register'), $html);
    }

    public function test_field_photos_are_local_derivatives_with_alt_text(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['kkn-itb-2025-mahasiswa-jalan-desa', 'kkn-itb-2025-rombongan-hujan'] as $name) {
            $this->assertStringContainsString('assets/photography/field/'.$name.'-1600.webp', $html);
            $this->assertFileExists(public_path('assets/photography/field/'.$name.'-800.webp'));
        }
        $this->assertSame(2, substr_count($html, 'class="sg-figure"'));
        $this->assertStringContainsString('alt="Mahasiswa berompi KKN ITB 2025', $html);
        // foto katalog versi pertama tidak lagi dipakai
        $this->assertStringNotContainsString('photography/homepage/', $html);
    }

    public function test_guest_calls_to_action_point_to_existing_routes(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['register', 'login', 'register.student', 'register.institution', 'register.company'] as $name) {
            $this->assertStringContainsString('href="'.route($name).'"', $html, $name);
        }
        $this->assertStringContainsString('data-hero-primary', $html);
    }

    public function test_logged_in_user_gets_dashboard_action_and_no_role_choice(): void
    {
        $student = $this->makeStudent('Sari', 'Wulandari');

        $html = $this->actingAs($student->user)->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('student.dashboard').'"', $html);
        $this->assertStringNotContainsString('Pilih peran saat mendaftar', $html);
    }
}
