<?php

namespace Tests\Feature\UiAudit;

use App\Models\Problem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * audit ui/ux tahap 1: judul tab tidak ter-escape dua kali (F-01), tooltip tombol ikon (F-25),
 * latar menu mobile (F-26), ejaan footer (F-29).
 */
class QuickWinsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function title(string $html): string
    {
        preg_match('#<title>([^<]*)</title>#', $html, $m);

        return $m[1] ?? '';
    }

    public function test_title_with_ampersand_is_escaped_exactly_once(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');
        $html = $this->actingAs($student->user)->get('/student/jobs')->assertOk()->getContent();

        $this->assertSame('Lowongan Kerja &amp; Magang — Student Go', $this->title($html));
        $this->assertStringNotContainsString('&amp;amp;', $html);
    }

    public function test_title_from_data_stays_escaped(): void
    {
        // isi judul dari data tetap aman: hanya satu kali escape, tidak pernah mentah
        $institution = $this->makeInstitution();
        [$problemId] = $this->makeOpenProblems($institution->id, 1);
        Problem::whereKey($problemId)->update(['title' => 'Bank Sampah & <b>Zero</b> "Waste" Jum\'at']);
        $student = $this->makeStudent('Farah', 'Wibowo');

        $html = $this->actingAs($student->user)->get('/student/browse-problems/'.$problemId)->assertOk()->getContent();

        $this->assertSame('Bank Sampah &amp; &lt;b&gt;Zero&lt;/b&gt; &quot;Waste&quot; Jum&#039;at — Student Go', $this->title($html));
    }

    public function test_icon_button_shows_its_label_as_tooltip(): void
    {
        $html = Blade::render('<x-ui.icon-button icon="eye" label="Lihat lamaran Rina" href="/x" />');

        $this->assertStringContainsString('aria-label="Lihat lamaran Rina"', $html);
        $this->assertStringContainsString('title="Lihat lamaran Rina"', $html);

        // title dari pemanggil tetap dipakai
        $own = Blade::render('<x-ui.icon-button icon="eye" label="A" title="B" />');
        $this->assertStringContainsString('title="B"', $own);
        $this->assertStringNotContainsString('title="A"', $own);
    }

    public function test_mobile_menu_has_backdrop_and_footer_spelling(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');
        $html = $this->actingAs($student->user)->get('/notifications')->assertOk()->getContent();

        $this->assertStringContainsString('class="sg-mobile-nav__backdrop"', $html);
        $this->assertStringContainsString('Direktorat Jenderal Kekayaan Intelektual', $html);
        $this->assertStringNotContainsString('Jendral', $html);
    }
}
