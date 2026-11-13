<?php

namespace Tests\Feature\UiAudit;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * audit ui/ux tahap 2: tombol bilah filter setinggi kontrol (F-04) dan atribut tumpuk tabel (F-06).
 */
class KomponenKontrolTabelTest extends TestCase
{
    public function test_filter_bar_buttons_match_control_height(): void
    {
        $html = Blade::render('<x-ui.filter-bar action="/x" reset-href="/x"><span>bidang</span></x-ui.filter-bar>');

        $this->assertMatchesRegularExpression('#<button type="submit" class="sg-btn sg-btn--primary sg-btn--lg">Terapkan</button>#', $html);
        $this->assertMatchesRegularExpression('#class="sg-btn sg-btn--tertiary sg-btn--lg">Atur ulang</a>#', $html);
    }

    public function test_table_stack_attributes_are_styled(): void
    {
        $css = file_get_contents(resource_path('css/student-go/components.css'));

        $this->assertStringContainsString('td[data-sg-stack="bare"]', $css);
        $this->assertStringContainsString('td[data-sg-stack="hide"] { display: none; }', $css);
        // kata biasa tidak lagi dipenggal di tengah pada sel tabel
        $this->assertMatchesRegularExpression('#\.sg-table td \{[^}]*overflow-wrap: break-word;#', $css);
    }
}
