<?php

namespace Tests\Feature\Phase7;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 7: dashboard mahasiswa sebagai antrean kerja.
 * header + langkah berikutnya + rekomendasi (maks 3) + ringkasan jaringan + proyek berjalan + lamaran terbaru;
 * tanpa hero foto, tile, statistik ganda atau chart (D-10, D-21); status dari kosakata bersama (D-02).
 */
class StudentDashboardTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private int $institutionId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->institutionId = $this->makeInstitution()->id;
    }

    private function dashboard(Student $student): string
    {
        return $this->actingAs($student->user)->get('/student/dashboard')->assertOk()->getContent();
    }

    // isi <main> saja, supaya navbar/footer tidak ikut dinilai
    private function main(string $html): string
    {
        $start = strpos($html, '<main');

        return substr($html, $start, strpos($html, '</main>') - $start);
    }

    private function apply(Student $student, int $problemId, string $status, string $appliedAt = '2026-10-01 09:00:00'): int
    {
        return DB::table('applications')->insertGetId([
            'student_id' => $student->id,
            'problem_id' => $problemId,
            'motivation' => 'Motivasi',
            'status' => $status,
            'applied_at' => $appliedAt,
            'created_at' => $appliedAt,
            'updated_at' => $appliedAt,
        ]);
    }

    private function openProblem(array $attrs = []): int
    {
        $id = $this->makeOpenProblems($this->institutionId, 1)[0];
        DB::table('problems')->where('id', $id)->update(array_merge([
            'application_deadline' => now()->addDays(20)->toDateString(),
        ], $attrs));

        return $id;
    }

    public function test_work_queue_replaces_hero_tiles_and_duplicate_statistics(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');
        $a = $this->openProblem(['title' => 'Bimbingan Belajar Gratis']);
        $b = $this->openProblem(['title' => 'Mitigasi Bencana Berbasis Masyarakat']);
        $c = $this->openProblem(['title' => 'Diversifikasi Pangan Lokal']);
        $this->apply($student, $a, 'pending', '2026-10-04 09:00:00');
        $this->apply($student, $b, 'pending', '2026-09-24 09:00:00');
        $rejected = $this->apply($student, $c, 'rejected', '2026-10-18 09:00:00');

        $html = $this->dashboard($student);
        $main = $this->main($html);

        // satu h1 dengan nama kanonis, aksi utama di header
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertMatchesRegularExpression('/<h1>\s*Selamat datang, Farah Wibowo\s*<\/h1>/', $main);
        $this->assertStringContainsString('href="'.route('student.browse-problems.index').'"', $main);
        $this->assertStringContainsString('href="'.route('student.applications.index').'"', $main);

        // antrean: jumlah lamaran menunggu = query, tautan ke daftar yang sudah difilter
        $this->assertStringContainsString('2 lamaran menunggu keputusan instansi', $main);
        $this->assertStringContainsString('href="'.route('student.applications.index', ['status' => 'pending']).'"', $main);
        $this->assertStringContainsString('Lengkapi profil Anda', $main);
        $this->assertStringContainsString('href="'.route('student.profile.edit').'"', $main);

        // tabel lamaran terbaru: status satu kata dari kosakata bersama, tautan detail tetap
        $this->assertStringContainsString('Lima lamaran KKN terbaru', $main);
        $this->assertSame(2, substr_count($main, 'data-value="pending"'));
        $this->assertSame(1, substr_count($main, 'data-value="rejected"'));
        $this->assertStringContainsString('Menunggu', $main);
        $this->assertStringContainsString('Ditolak', $main);
        $this->assertStringContainsString('href="'.route('student.applications.show', $rejected).'"', $main);
        $this->assertStringContainsString('18 Okt 2026', $main);

        // yang dihapus: hero foto, tile statistik, bar, tombol hubungkan, kata status inggris, gaya inline
        foreach (['dashboard-student2.jpeg', 'Statistik Aktivitas', 'stat-bar', 'Total Aplikasi', 'Hubungkan', 'Rejected', 'Pending', 'Space+Grotesk', '<style', 'style="', '<canvas'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, $gone);
        }
    }

    public function test_empty_account_gets_a_clear_next_step(): void
    {
        $student = $this->makeStudent('Agus', 'Maharani');

        $main = $this->main($this->dashboard($student));

        $this->assertStringContainsString('Belum ada lamaran KKN', $main);
        $this->assertStringContainsString('Telusuri masalah KKN', $main);
        $this->assertStringContainsString('empty-states/no-applications.svg', $main);
        $this->assertStringNotContainsString('lamaran menunggu', $main);
        $this->assertStringNotContainsString('Lima lamaran KKN terbaru', $main);
        $this->assertStringNotContainsString('Proyek berjalan', $main);
        // tidak ada tile nol: jaringan hanya satu baris ringkasan, permintaan/saran nol tidak ditulis
        $this->assertMatchesRegularExpression('/<b>0<\/b> koneksi/', $main);
        $this->assertStringNotContainsString('permintaan</li>', $main);
        $this->assertStringNotContainsString('saran</li>', $main);
    }

    public function test_complete_profile_without_work_shows_the_quiet_queue_line(): void
    {
        $student = $this->makeStudent('Rina', 'Lestari', null, [
            'profile_photo_path' => 'students/rina.jpg', 'bio' => 'Bio', 'skills' => ['Riset'], 'whatsapp_number' => '0812', 'semester' => 5,
        ]);

        $main = $this->main($this->dashboard($student));

        $this->assertStringContainsString('Tidak ada yang perlu Anda tindak lanjuti saat ini.', $main);
        $this->assertStringNotContainsString('Lengkapi profil Anda', $main);
    }

    public function test_skills_saved_as_array_no_longer_break_the_dashboard(): void
    {
        // sebelum phase 7: json_decode(array) -> TypeError -> 500 setelah mahasiswa mengisi keahlian
        $student = $this->makeStudent('Dewi', 'Saputri', null, ['skills' => ['Python', 'Desain Grafis']]);
        $match = $this->openProblem(['title' => 'Sistem Informasi Desa', 'required_skills' => json_encode(['Python']), 'required_majors' => null]);
        $this->openProblem(['title' => 'Pelatihan Menjahit', 'required_skills' => json_encode(['Menjahit']), 'required_majors' => null]);

        $main = $this->main($this->dashboard($student));

        $this->assertStringContainsString('Sistem Informasi Desa', $main);
        $this->assertStringContainsString('href="'.route('student.browse-problems.show', $match).'"', $main);
        $this->assertStringNotContainsString('Pelatihan Menjahit', $main);
    }

    public function test_recommendations_are_three_rows_with_a_quiet_view_action(): void
    {
        $student = $this->makeStudent('Budi', 'Azzahra');
        foreach (range(1, 5) as $i) {
            $this->openProblem(['title' => 'Masalah Rekomendasi '.$i, 'required_majors' => null]);
        }

        $main = $this->main($this->dashboard($student));

        $this->assertSame(3, substr_count($main, 'class="sg-dash-reco__item"'));
        $this->assertSame(3, preg_match_all('/class="sg-btn sg-btn--tertiary"[^>]*>\s*Lihat<span class="sr-only">/', $main));
        $this->assertStringContainsString('Ditutup <time', $main);
        $this->assertStringNotContainsString('sg-btn--primary">Lihat', $main);
    }

    public function test_active_project_puts_the_nearest_milestone_first(): void
    {
        $student = $this->makeStudent('Sari', 'Wulandari');
        $problemId = $this->openProblem(['title' => 'Inkubasi Bisnis Desa']);
        $this->makeProject($student, $this->institutionId, $problemId);
        $projectId = DB::table('projects')->where('student_id', $student->id)->value('id');
        DB::table('projects')->where('id', $projectId)->update(['progress_percentage' => 42]);
        foreach ([['Evaluasi Dan Dokumentasi', 30], ['Pelaksanaan Kegiatan Utama', 11], ['Persiapan Selesai', 3, 'completed']] as $m) {
            DB::table('project_milestones')->insert([
                'project_id' => $projectId, 'title' => $m[0], 'target_date' => now()->addDays($m[1])->toDateString(),
                'status' => $m[2] ?? 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $main = $this->main($this->dashboard($student));

        $kicker = strpos($main, 'Tenggat terdekat');
        $this->assertNotFalse($kicker);
        $this->assertLessThan(strpos($main, 'Evaluasi Dan Dokumentasi'), strpos($main, 'Pelaksanaan Kegiatan Utama'));
        $this->assertLessThan(strpos($main, 'Pelaksanaan Kegiatan Utama'), $kicker);
        $this->assertStringContainsString('11 hari lagi', $main);
        $this->assertStringNotContainsString('Persiapan Selesai', $main);
        $this->assertStringContainsString('href="'.route('student.projects.show', $projectId).'"', $main);

        // proyek berjalan (status 'active', bukan 'in_progress' seperti view lama) dengan progres dari kolom nyata
        $this->assertStringContainsString('Proyek berjalan', $main);
        $this->assertStringContainsString('value="42"', $main);
        $this->assertStringContainsString('href="'.route('student.projects.index').'"', $main);
    }

    public function test_friend_requests_keep_their_endpoints_and_connect_moves_to_the_network_page(): void
    {
        $student = $this->makeStudent('Nina', 'Putri');
        $requester = $this->makeStudent('Yudi', 'Rachman');
        $friend = $this->makeStudent('Putri', 'Sari');
        $this->makeFriendship($requester, $student, 'pending');
        $this->makeFriendship($student, $friend, 'accepted');
        $friendshipId = DB::table('friends')->where('requester_id', $requester->id)->value('id');

        $main = $this->main($this->dashboard($student));

        $this->assertStringContainsString('Yudi Rachman ingin terhubung', $main);
        $this->assertMatchesRegularExpression('/<form method="POST" action="'.preg_quote(route('student.friends.accept', $friendshipId), '/').'">\s*<input type="hidden" name="_token"/', $main);
        $this->assertMatchesRegularExpression('/<form method="POST" action="'.preg_quote(route('student.friends.reject', $friendshipId), '/').'">\s*<input type="hidden" name="_token"/', $main);

        // ringkasan jaringan = query (1 koneksi, 1 permintaan); hubungkan tidak lagi di dashboard
        $this->assertMatchesRegularExpression('/<b>1<\/b> koneksi/', $main);
        $this->assertMatchesRegularExpression('/<b>1<\/b> permintaan/', $main);
        $this->assertStringContainsString('href="'.route('student.friends.index').'"', $main);
        $this->assertStringNotContainsString('/student/friends/request/', $main);
    }

    public function test_accepting_a_request_from_the_dashboard_still_works(): void
    {
        $student = $this->makeStudent('Nina', 'Putri');
        $requester = $this->makeStudent('Yudi', 'Rachman');
        $this->makeFriendship($requester, $student, 'pending');
        $friendshipId = DB::table('friends')->value('id');

        $this->actingAs($student->user)
            ->from('/student/dashboard')
            ->post(route('student.friends.accept', $friendshipId))
            ->assertRedirect('/student/dashboard');

        $this->assertSame('accepted', DB::table('friends')->where('id', $friendshipId)->value('status'));
    }

    public function test_other_roles_and_guests_keep_their_redirects(): void
    {
        $this->get('/student/dashboard')->assertRedirect(route('login'));

        $company = $this->makeCompany();
        $this->actingAs($company->user)->get('/student/dashboard')->assertRedirect();
    }
}
