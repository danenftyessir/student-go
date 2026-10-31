<?php

namespace Tests\Feature\Phase4;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * AD-11: pembuka beranda dengan video latar berulang. poster sebagai gambar pertama, video tanpa src di html
 * (dimuat oleh skrip), header transparan hanya di beranda, tanpa embed pihak ketiga dan tanpa angka.
 */
class HeroVideoTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-31 09:00:00');
        $this->seedRegion();
    }

    private function hero(string $html): string
    {
        $this->assertSame(1, preg_match('#<section class="sg-hero".*?</section>#s', $html, $m));

        return $m[0];
    }

    public function test_hero_has_one_h1_reused_copy_and_existing_routes(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $hero = $this->hero($html);

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<h1 id="home-title" class="sg-hero__title">Platform digital untuk Kuliah Kerja Nyata</h1>', $hero);
        // lead menjelaskan peran, tidak mengulang judul (draf d-23 b, dari fungsi produk yang ada)
        $this->assertStringContainsString('Student Go mempertemukan instansi dengan mahasiswa KKN.', $hero);
        $this->assertSame(1, substr_count(strip_tags($hero), 'Platform digital'));
        $this->assertStringContainsString('href="'.route('register').'"', $hero);
        $this->assertStringContainsString('href="'.route('login').'"', $hero);
        $this->assertStringContainsString('data-hero-primary', $hero);
    }

    public function test_video_is_decorative_local_and_not_loaded_by_the_markup(): void
    {
        $hero = $this->hero($this->get('/')->assertOk()->getContent());

        $this->assertSame(1, preg_match('#<video[^>]*>#', $hero, $video));
        foreach (['muted', 'loop', 'playsinline', 'preload="none"', 'aria-hidden="true"', 'tabindex="-1"'] as $attr) {
            $this->assertStringContainsString($attr, $video[0], $attr);
        }
        // tanpa src/source: tidak ada permintaan video sampai skrip memutuskan (gerak dikurangi, hemat data)
        $this->assertStringNotContainsString('src=', $video[0]);
        $this->assertStringNotContainsString('<source src', $hero);
        foreach (['desktop-webm', 'desktop-mp4', 'mobile-webm', 'mobile-mp4'] as $key) {
            $this->assertMatchesRegularExpression('#data-video-'.$key.'="[^"]*/video/hero-loop-[a-z]+\.(webm|mp4)"#', $hero, $key);
        }
        // poster: lcp, ukuran tetap, alt kosong
        $this->assertMatchesRegularExpression('#<img class="sg-hero__poster" src="[^"]*/video/hero-loop-desktop\.jpg" alt="" width="1920" height="1080" fetchpriority="high">#', $hero);
        $this->assertStringContainsString('media="(max-width: 767px)" type="image/webp"', $hero);
        // tanpa embed pihak ketiga
        foreach (['youtube', 'vimeo', '<iframe'] as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $hero, $needle);
        }
    }

    public function test_every_referenced_hero_file_exists_and_fits_the_budget(): void
    {
        $budgets = [
            'hero-loop-desktop.webm' => 3.5e6, 'hero-loop-desktop.mp4' => 5e6,
            'hero-loop-mobile.webm' => 2e6, 'hero-loop-mobile.mp4' => 2e6,
            'hero-loop-desktop.webp' => 120e3, 'hero-loop-desktop.jpg' => 120e3,
            'hero-loop-mobile.webp' => 120e3, 'hero-loop-mobile.jpg' => 120e3,
        ];
        foreach ($budgets as $file => $max) {
            $path = public_path('video/'.$file);
            $this->assertFileExists($path);
            $this->assertLessThanOrEqual($max, filesize($path), $file);
        }
    }

    public function test_hero_has_no_pause_button_and_keeps_the_header_sentinel(): void
    {
        $hero = $this->hero($this->get('/')->assertOk()->getContent());

        $this->assertStringNotContainsString('data-sg-hero-toggle', $hero);
        $this->assertStringNotContainsString('<button', $hero);
        $this->assertStringContainsString('data-sg-hero-sentinel', $hero);
    }

    public function test_hero_carries_no_statistics(): void
    {
        $institution = $this->makeInstitution();
        $this->makeOpenProblems($institution->id, 3);

        $text = preg_replace('/\s+/', ' ', strip_tags($this->hero($this->get('/')->assertOk()->getContent())));

        $this->assertDoesNotMatchRegularExpression('/\d/', $text);
    }

    public function test_transparent_header_only_on_the_homepage(): void
    {
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('class="sg-header sg-header--hero is-over-hero"', $home);
        $this->assertStringContainsString('assets/brand/student-go-logo-on-dark.svg', $home);
        $this->assertStringContainsString('data-sg-header', $home);

        $about = $this->get(route('about'))->assertOk()->getContent();
        $this->assertStringNotContainsString('sg-header--hero', $about);
        $this->assertStringNotContainsString('data-sg-header', $about);
    }

    public function test_logged_in_user_gets_dashboard_action_in_the_hero(): void
    {
        $student = $this->makeStudent('Sari', 'Wulandari');

        $hero = $this->hero($this->actingAs($student->user)->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('href="'.route('student.dashboard').'"', $hero);
        $this->assertStringNotContainsString('href="'.route('register').'"', $hero);
    }
}
