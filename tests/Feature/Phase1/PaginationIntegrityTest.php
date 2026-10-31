<?php

namespace Tests\Feature\Phase1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * J2 / J4: setiap list berhalaman harus deterministik (tie-breaker id),
 * setiap record muncul tepat sekali, dan total sama dengan jumlah yang bisa dicapai
 */
class PaginationIntegrityTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    /**
     * ambil query select utama (yang memakai limit/offset) selama request
     */
    private function capturePagedSql(callable $request): array
    {
        $sql = [];
        DB::listen(function ($query) use (&$sql) {
            if (str_contains(strtolower($query->sql), 'limit')) {
                $sql[] = strtolower($query->sql);
            }
        });
        $request();

        return $sql;
    }

    private function assertTieBreaker(array $sqls, string $table): void
    {
        $match = collect($sqls)->first(fn ($s) => str_contains($s, 'from "' . $table . '"') && str_contains($s, 'order by'));
        $this->assertNotNull($match, "query berhalaman untuk {$table} tidak ditemukan");
        $this->assertMatchesRegularExpression('/order by .*"' . $table . '"\."id" (asc|desc)|order by .*"id" (asc|desc)/', $match, "query {$table} tidak memakai tie-breaker id: {$match}");
    }

    public function test_browse_problems_pages_return_each_open_problem_exactly_once(): void
    {
        $viewer = $this->makeStudent('Ayu', 'Lestari');
        $institution = $this->makeInstitution();
        $ids = $this->makeOpenProblems($institution->id, 30);

        $seen = [];
        $sqls = $this->capturePagedSql(function () use ($viewer, &$seen, &$total) {
            for ($page = 1; $page <= 3; $page++) {
                $response = $this->actingAs($viewer->user)->get('/student/browse-problems?page=' . $page);
                $response->assertOk();
                $paginator = $response->viewData('problems');
                $total = $response->viewData('totalProblems');
                $this->assertSame($paginator->total(), $total, 'label total dan paginator harus sama');
                foreach ($paginator->items() as $item) {
                    $seen[] = $item->id;
                }
            }
        });

        $this->assertSame(30, $total);
        $this->assertCount(30, $seen);
        $this->assertSame(count($seen), count(array_unique($seen)), 'ada problem yang muncul dua kali');
        $this->assertEqualsCanonicalizing($ids, $seen);
        $this->assertTieBreaker($sqls, 'problems');
    }

    public function test_repository_pages_return_each_document_exactly_once_and_header_matches(): void
    {
        $viewer = $this->makeStudent('Budi', 'Santoso');
        $ids = [];
        for ($i = 1; $i <= 26; $i++) {
            $ids[] = DB::table('documents')->insertGetId([
                'uploaded_by' => $viewer->user_id,
                'title' => 'Dokumen ' . $i,
                'file_path' => 'documents/' . $i . '.pdf',
                'file_type' => 'pdf',
                'file_size' => 1000,
                'is_public' => true,
                'status' => 'approved',
                'province_id' => $this->provinceId,
                'created_at' => '2026-10-01 10:00:00',
                'updated_at' => '2026-10-01 10:00:00',
            ]);
        }
        // dokumen yang tidak boleh terhitung
        DB::table('documents')->insert([
            'uploaded_by' => $viewer->user_id, 'title' => 'Draf', 'file_path' => 'x.pdf', 'file_type' => 'pdf',
            'file_size' => 1, 'is_public' => true, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);

        // halaman penuh memakai EXTRACT(YEAR ...) khusus PostgreSQL (diverifikasi live);
        // di sini query daftar yang sama diuji langsung
        $controller = app(\App\Http\Controllers\Student\KnowledgeRepositoryController::class);
        $request = \Illuminate\Http\Request::create('/student/repository', 'GET');
        $total = (clone $controller->listingQuery($request))->count();
        $header = \App\Models\Document::where('is_public', true)->where('status', 'approved')->count();

        $seen = [];
        $sqls = $this->capturePagedSql(function () use ($controller, $request, &$seen) {
            for ($page = 1; $page <= 3; $page++) {
                $paginator = $controller->listingQuery($request)->paginate(12, ['*'], 'page', $page);
                foreach ($paginator->items() as $item) {
                    $seen[] = $item->id;
                }
            }
        });

        $this->assertSame(26, $total);
        $this->assertSame(26, $header, 'header repositori harus sama dengan jumlah yang bisa dicapai');
        $this->assertCount(26, $seen);
        $this->assertSame(count($seen), count(array_unique($seen)));
        $this->assertEqualsCanonicalizing($ids, $seen);
        $this->assertTieBreaker($sqls, 'documents');
    }

    public function test_friends_search_is_ordered_by_name_then_id_and_pages_do_not_overlap(): void
    {
        $viewer = $this->makeStudent('Zaki', 'Viewer');
        $ids = [];
        for ($i = 1; $i <= 45; $i++) {
            // nama sengaja kembar agar tie-breaker diuji
            $ids[] = $this->makeStudent('Sari', 'Putri')->id;
        }

        $seen = [];
        $sqls = $this->capturePagedSql(function () use ($viewer, &$seen) {
            for ($page = 1; $page <= 3; $page++) {
                $response = $this->actingAs($viewer->user)->get('/student/friends/search?page=' . $page);
                $response->assertOk();
                foreach ($response->viewData('results')->items() as $item) {
                    $seen[] = $item->id;
                }
            }
        });

        $this->assertCount(45, $seen);
        $this->assertSame(count($seen), count(array_unique($seen)));
        $this->assertEqualsCanonicalizing($ids, $seen);
        $this->assertTieBreaker($sqls, 'students');
    }

    public function test_company_talents_json_pages_are_disjoint_and_total_is_the_real_pool(): void
    {
        $company = $this->makeCompany();
        $institution = $this->makeInstitution();
        $problemIds = $this->makeOpenProblems($institution->id, 1);

        $poolUserIds = [];
        for ($i = 1; $i <= 15; $i++) {
            $student = $this->makeStudent('Talenta', 'Ke' . $i, null, [], ['created_at' => '2026-10-01 10:00:00']);
            $this->makeProject($student, $institution->id, $problemIds[0]);
            $poolUserIds[] = $student->user_id;
        }
        // mahasiswa tanpa proyek bukan bagian dari pool
        $this->makeStudent('Tanpa', 'Proyek');

        $seen = [];
        $sqls = $this->capturePagedSql(function () use ($company, &$seen, &$total) {
            for ($page = 1; $page <= 2; $page++) {
                $response = $this->actingAs($company->user)
                    ->getJson('/company/talents?page=' . $page . '&per_page=12', ['X-Requested-With' => 'XMLHttpRequest']);
                $response->assertOk();
                $total = $response->json('total');
                foreach ($response->json('talents') as $talent) {
                    $seen[] = $talent['id'];
                }
            }
        });

        $this->assertSame(15, $total);
        $this->assertCount(15, $seen);
        $this->assertSame(count($seen), count(array_unique($seen)));
        $this->assertEqualsCanonicalizing($poolUserIds, $seen);
        $this->assertTieBreaker($sqls, 'users');
    }

    public function test_company_talents_page_states_the_real_pool_size_and_rule(): void
    {
        $company = $this->makeCompany();
        $institution = $this->makeInstitution();
        $problemIds = $this->makeOpenProblems($institution->id, 1);
        for ($i = 1; $i <= 14; $i++) {
            $this->makeProject($this->makeStudent('Pool', 'Ke' . $i), $institution->id, $problemIds[0]);
        }

        $response = $this->actingAs($company->user)->get('/company/talents');
        $response->assertOk();
        $this->assertSame(14, $response->viewData('totalTalents'));
        // pool sebenarnya dan aturan yang mempersempitnya harus tertulis di halaman
        // phase 22: jumlah dirender server (sebelumnya variabel alpine 'totalTalents: 14')
        $response->assertSee('14 mahasiswa', false);
        $response->assertSee('mahasiswa dengan minimal 1 proyek');
    }
}
