<?php

namespace Tests\Feature\UiAudit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * audit ui/ux: bagian yang dimuat lewat js punya kerangka saat memuat dan pesan saat gagal (bukan area kosong).
 * halaman lain dirender server sehingga isinya datang bersama html.
 */
class SkeletonLoadingTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    public function test_notification_panel_has_skeleton_and_failure_message(): void
    {
        $this->seedRegion();
        $student = $this->makeStudent('Farah', 'Wibowo');
        $html = $this->actingAs($student->user)->get('/notifications')->assertOk()->getContent();

        $this->assertStringContainsString('<template x-if="! loaded && ! failed">', $html);
        $this->assertMatchesRegularExpression('#role="status" aria-label="Memuat notifikasi…">\s*<span class="sg-skeleton sg-skeleton--title"></span>#u', $html);
        $this->assertStringContainsString('<template x-if="! loaded && failed">', $html);
        $this->assertStringContainsString('Notifikasi belum bisa dimuat.', $html);

        $js = file_get_contents(resource_path('js/student-go/alpine.js'));
        $this->assertStringContainsString('this.failed = true;', $js);
    }

    public function test_home_map_shows_loading_until_leaflet_is_ready(): void
    {
        $blade = file_get_contents(resource_path('views/home/index.blade.php'));

        $this->assertStringContainsString('<x-ui.loading label="Memuat peta…" class="sg-home-map__loading" />', $blade);
        $this->assertStringContainsString("loading?.remove();", $blade);
        $this->assertStringContainsString('Peta belum bisa dimuat.', $blade);
    }
}
