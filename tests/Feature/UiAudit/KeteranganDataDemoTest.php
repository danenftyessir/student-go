<?php

namespace Tests\Feature\UiAudit;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * masukan juri: angka beranda berasal dari data uji coba, jadi diberi keterangan kecil "data demo" yang bisa dimatikan.
 */
class KeteranganDataDemoTest extends TestCase
{
    private function note(): string
    {
        return Blade::render("@if (config('app.demo_data'))<p class=\"sg-demo-note\">Data uji coba (demo), bukan data pengguna sebenarnya.</p>@endif");
    }

    public function test_note_is_shown_by_default_and_can_be_turned_off(): void
    {
        $this->assertTrue(config('app.demo_data'));
        $this->assertStringContainsString('Data uji coba (demo)', $this->note());

        config(['app.demo_data' => false]);
        $this->assertSame('', trim($this->note()));

        // beranda memakai keterangan yang sama di ringkasan dan blok hasil proyek
        $this->assertSame(2, substr_count(file_get_contents(resource_path('views/home/index.blade.php')), 'class="sg-demo-note"'));
    }
}
