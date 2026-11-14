<?php

namespace Tests\Feature\UiAudit;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * halaman bandingkan talenta: tombol simpan menampilkan status awal (Tersimpan) untuk talenta yang sudah disimpan.
 */
class BandingkanTalentaSimpanTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    public function test_saved_talent_starts_as_tersimpan(): void
    {
        $this->seedRegion();
        $company = $this->makeCompany();
        $a = $this->makeStudent('Mira', 'Susanto');
        $b = $this->makeStudent('Luki', 'Maharani');
        DB::table('saved_talents')->insert(['company_id' => $company->id, 'user_id' => $a->user_id, 'saved_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $html = $this->actingAs($company->user)
            ->get(route('company.talents.compare', ['ids' => $a->user_id.','.$b->user_id]))->assertOk()->getContent();

        // @js(array) dirender sebagai JSON.parse('[…]')
        $this->assertStringContainsString("sgTalentCompare(JSON.parse('[".$a->user_id."]'))", html_entity_decode($html));
        $this->assertSame(1, substr_count($html, 'aria-pressed="true"'));
        $this->assertSame(1, substr_count($html, 'aria-pressed="false"'));
        $this->assertMatchesRegularExpression('#aria-pressed="true".*?>Tersimpan</span>#s', $html);
    }
}
