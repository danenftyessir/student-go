<?php

namespace Tests\Feature\UiAudit;

use App\Models\Problem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * audit ui/ux tahap 4: status pertemanan di pencarian koneksi diambil dalam satu query (F-10),
 * dengan hasil yang sama persis dengan pengecekan per mahasiswa.
 */
class EfisiensiQueryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    public function test_batch_friendship_status_matches_single_lookup(): void
    {
        $me = $this->makeStudent('Farah', 'Wibowo');
        $friend = $this->makeStudent('Budi', 'Santoso');
        $sent = $this->makeStudent('Citra', 'Lestari');
        $received = $this->makeStudent('Dedi', 'Pratama');
        $rejected = $this->makeStudent('Eka', 'Putri');
        $stranger = $this->makeStudent('Fajar', 'Nugroho');

        $this->makeFriendship($me, $friend, 'accepted');
        $this->makeFriendship($me, $sent, 'pending');
        $this->makeFriendship($received, $me, 'pending');
        $this->makeFriendship($me, $rejected, 'rejected');

        $ids = [$friend->id, $sent->id, $received->id, $rejected->id, $stranger->id];
        $batch = $me->friendshipStatusesWith($ids);

        foreach ($ids as $id) {
            $this->assertSame($me->friendshipStatusWith($id), $batch[$id], "student $id");
        }
        $this->assertSame(['friends', 'request_sent', 'request_received', 'none', 'none'], array_values($batch));
        $this->assertSame([], $me->friendshipStatusesWith([]));
    }

    public function test_status_counts_matches_one_count_per_status(): void
    {
        $institution = $this->makeInstitution();
        $ids = $this->makeOpenProblems($institution->id, 5);
        Problem::whereKey($ids[0])->update(['status' => 'draft']);
        Problem::whereKey($ids[1])->update(['status' => 'closed']);
        Problem::whereKey($ids[2])->update(['status' => 'closed']);
        $other = $this->makeInstitution('Pemerintah Desa');
        $this->makeOpenProblems($other->id, 3);

        $base = Problem::where('institution_id', $institution->id);
        $statuses = ['draft', 'open', 'in_progress', 'completed', 'closed'];
        $expected = ['total' => (clone $base)->count()];
        foreach ($statuses as $status) {
            $expected[$status] = (clone $base)->where('status', $status)->count();
        }

        $this->assertSame($expected, status_counts($base, $statuses));
        $this->assertSame(['total' => 5, 'draft' => 1, 'open' => 2, 'in_progress' => 0, 'completed' => 0, 'closed' => 2], status_counts($base, $statuses));
        // query dasar tidak berubah oleh helper
        $this->assertSame(5, $base->count());
    }

    public function test_search_page_queries_friends_table_once(): void
    {
        $me = $this->makeStudent('Farah', 'Wibowo');
        foreach (range(1, 8) as $i) {
            $other = $this->makeStudent('Mahasiswa', 'Uji'.$i);
            if ($i % 2) {
                $this->makeFriendship($me, $other, 'pending');
            }
        }

        $friendQueries = 0;
        DB::listen(function ($q) use (&$friendQueries) {
            if (preg_match('/from ["`]?friends["`]?/i', $q->sql)) {
                $friendQueries++;
            }
        });

        $this->actingAs($me->user)->get('/student/friends/search')->assertOk()->assertSee('Mahasiswa Uji1');

        $this->assertSame(1, $friendQueries);
    }
}
