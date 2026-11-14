<?php

namespace Tests\Feature\UiAudit;

use App\Models\Problem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * audit ui/ux F-09: masalah yang tenggatnya lewat ditandai "Pendaftaran ditutup" di daftar dan galeri
 * (query dan urutan tidak berubah).
 */
class MasalahLewatTenggatTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    public function test_expired_problem_is_flagged_and_open_problem_is_not(): void
    {
        $this->seedRegion();
        $institution = $this->makeInstitution();
        [$expired, $open] = $this->makeOpenProblems($institution->id, 2);
        Problem::whereKey($expired)->update(['title' => 'Masalah Lewat Tenggat', 'application_deadline' => now()->subDays(3)]);
        Problem::whereKey($open)->update(['title' => 'Masalah Masih Buka', 'application_deadline' => now()->addDays(10)]);
        $student = $this->makeStudent('Farah', 'Wibowo');

        // halaman memuat tampilan daftar dan galeri sekaligus: penanda muncul sekali di baris dan sekali di kartu masalah lewat
        $html = $this->actingAs($student->user)->get('/student/browse-problems')->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'Pendaftaran ditutup</span>'));

        preg_match('#<tr data-problem-id="'.$expired.'">.*?</tr>#s', $html, $row);
        $this->assertStringContainsString('Pendaftaran ditutup</span>', $row[0]);
        preg_match('#<tr data-problem-id="'.$open.'">.*?</tr>#s', $html, $row);
        $this->assertStringNotContainsString('Pendaftaran ditutup', $row[0]);
    }
}
