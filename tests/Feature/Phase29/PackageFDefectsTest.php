<?php

namespace Tests\Feature\Phase29;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 29 paket F: cacat C1 (id lamaran tak dikenal = 404) dan C2 (aktivitas teman tidak 500).
 * rute, nama rute dan cek kepemilikan tidak berubah.
 */
class PackageFDefectsTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->student = $this->makeStudent('Rina', 'Zahira');
    }

    private function apply(Student $student): int
    {
        $institutionId = $this->makeInstitution()->id;
        $problemId = $this->makeOpenProblems($institutionId, 1)[0];

        return DB::table('applications')->insertGetId([
            'student_id' => $student->id,
            'problem_id' => $problemId,
            'motivation' => 'Program KKN ini merupakan kesempatan untuk menerapkan ilmu di desa.',
            'status' => 'pending',
            'applied_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_c1_unknown_application_id_returns_404(): void
    {
        $this->actingAs($this->student->user)->get('/student/applications/999999')->assertNotFound();
    }

    public function test_c1_other_students_application_still_returns_404(): void
    {
        $foreign = $this->apply($this->makeStudent('Desi', 'Hartono'));

        $this->actingAs($this->student->user)->get('/student/applications/'.$foreign)->assertNotFound();
    }

    public function test_c1_own_application_still_opens(): void
    {
        $own = $this->apply($this->student);

        $this->actingAs($this->student->user)->get('/student/applications/'.$own)->assertOk();
    }

    public function test_c2_friend_activities_does_not_error_without_friends(): void
    {
        $this->actingAs($this->student->user)->get('/student/friends/activities')->assertOk();
    }

    public function test_c2_friend_activities_does_not_error_with_a_friend_project(): void
    {
        $friend = $this->makeStudent('Desi', 'Hartono');
        $this->makeFriendship($this->student, $friend);
        $institutionId = $this->makeInstitution()->id;
        $problemId = $this->makeOpenProblems($institutionId, 1)[0];
        $this->makeProject($friend, $institutionId, $problemId);
        $this->apply($friend);

        $response = $this->actingAs($this->student->user)->get('/student/friends/activities')->assertOk();

        $this->assertStringNotContainsString('placeholder-', $response->getContent());
        $project = collect($response->json('activities'))->firstWhere('type', 'project');
        $this->assertNotNull($project);
        $this->assertNull($project['preview']['image']);
    }

    public function test_c2_route_is_unchanged(): void
    {
        $this->assertSame(url('/student/friends/activities'), route('student.friends.activities'));
    }
}
