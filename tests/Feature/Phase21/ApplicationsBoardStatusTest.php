<?php

namespace Tests\Feature\Phase21;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 21 (disetujui user 2026-10-31): lamaran bernilai lama (pending dari formulir lamaran mahasiswa,
 * reviewed/accepted dari seeder) sebelumnya tidak masuk kolom mana pun di papan lamaran perusahaan.
 * kini masuk kolom setara: pending -> new, reviewed -> reviewing, accepted -> hired. data tidak diubah.
 */
class ApplicationsBoardStatusTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function companyWithApplications(): array
    {
        $company = $this->makeCompany();
        $jobId = DB::table('job_postings')->insertGetId([
            'company_id' => $company->id, 'title' => 'Lowongan Papan', 'slug' => 'lowongan-papan',
            'status' => 'posted', 'published_at' => now()->subDays(5), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $ids = [];
        foreach (['pending', 'new', 'reviewed', 'reviewing', 'accepted', 'hired', 'shortlisted', 'rejected'] as $i => $status) {
            $student = $this->makeStudent('Papan', 'Status' . $i);
            $ids[$status] = DB::table('job_applications')->insertGetId([
                'job_posting_id' => $jobId, 'user_id' => $student->user_id, 'status' => $status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return [$company, $ids];
    }

    private function columns(Company $company, array $query = []): array
    {
        $response = $this->actingAs($company->user)->get(route('company.applications.index', $query))->assertOk();

        return collect($response->viewData('applicationsByStatus'))
            ->map(fn ($apps) => $apps->pluck('id')->sort()->values()->all())
            ->all();
    }

    public function test_every_stored_status_appears_in_a_board_column(): void
    {
        [$company, $ids] = $this->companyWithApplications();
        $columns = $this->columns($company);

        $this->assertSame([$ids['pending'], $ids['new']], $columns['new']);
        $this->assertSame([$ids['reviewed'], $ids['reviewing']], $columns['reviewing']);
        $this->assertSame([$ids['accepted'], $ids['hired']], $columns['hired']);
        $this->assertSame([$ids['shortlisted']], $columns['shortlisted']);
        $this->assertSame([$ids['rejected']], $columns['rejected']);
        $this->assertSame(8, collect($columns)->flatten()->count(), 'tidak ada lamaran yang hilang dari papan');
    }

    public function test_status_filter_and_counts_include_equivalent_values(): void
    {
        [$company, $ids] = $this->companyWithApplications();

        $columns = $this->columns($company, ['status' => 'new']);
        $this->assertSame([$ids['pending'], $ids['new']], $columns['new']);
        $this->assertSame(2, collect($columns)->flatten()->count());

        // filter dengan nilai lama tetap bekerja dan muncul di kolom setara
        $this->assertSame([$ids['pending']], $this->columns($company, ['status' => 'pending'])['new']);

        $stats = $this->actingAs($company->user)->get(route('company.applications.index'))->viewData('stats');
        $this->assertSame(['total' => 8, 'new' => 2, 'shortlisted' => 1, 'hired' => 2], $stats);
    }

    public function test_stored_values_are_not_rewritten(): void
    {
        [$company, $ids] = $this->companyWithApplications();
        $this->columns($company);

        $this->assertSame('pending', DB::table('job_applications')->where('id', $ids['pending'])->value('status'));
        $this->assertSame('accepted', DB::table('job_applications')->where('id', $ids['accepted'])->value('status'));
    }
}
