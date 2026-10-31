<?php

namespace Tests\Feature\Phase12;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 12: profil, edit profil dan profil publik mahasiswa sebagai portofolio
 */
class StudentPortfolioTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->student = $this->makeStudent('Farah', 'Wibowo', 'Candra Hasanah', [
            'phone' => '+6281299887766',
            'bio' => 'Mahasiswa teknik lingkungan.',
            'skills' => ['Pemetaan GIS', 'Survei warga'],
            'interests' => ['Air bersih'],
            'stories' => ['Saya belajar mendengarkan warga Desa Sukamaju.'],
            'semester' => 7,
        ]);
    }

    private function makeCompletedProject(bool $visible = true, string $title = 'Air Bersih Desa Sukamaju'): void
    {
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        DB::table('problems')->where('id', $problemId)->update(['title' => $title, 'status' => 'completed']);
        $this->makeProject($this->student, $institution->id, $problemId);
        DB::table('projects')->where('problem_id', $problemId)->update([
            'status' => 'completed',
            'actual_end_date' => '2026-10-15',
            'is_portfolio_visible' => $visible,
        ]);
    }

    public function test_edit_form_loads_every_stored_value(): void
    {
        $html = $this->actingAs($this->student->user)->get('/student/profile/edit')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/name="first_name"\s+value="Farah"/', $html);
        $this->assertMatchesRegularExpression('/name="last_name"\s+value="Wibowo"/', $html);
        $this->assertStringContainsString('value="' . $this->student->user->email . '"', $html);
        $this->assertMatchesRegularExpression('/<option value="' . $this->universityId . '"\s+selected/', $html);
        $this->assertStringContainsString('value="Teknik Informatika"', $html);
        $this->assertStringContainsString('value="' . $this->student->nim . '"', $html);
        $this->assertMatchesRegularExpression('/<option value="7"\s+selected/', $html);
        $this->assertStringContainsString('value="+6281299887766"', $html);
        $this->assertStringContainsString('Mahasiswa teknik lingkungan.</textarea>', $html);
        $this->assertStringContainsString('Saya belajar mendengarkan warga Desa Sukamaju.</textarea>', $html);
        $this->assertStringContainsString('value="Pemetaan GIS"', $html);
        $this->assertStringContainsString('value="Survei warga"', $html);
        $this->assertStringContainsString('value="Air bersih"', $html);
    }

    public function test_edit_form_keeps_field_names_sections_and_separate_password_form(): void
    {
        $html = $this->actingAs($this->student->user)->get('/student/profile/edit')->assertOk()->getContent();

        foreach (['profile_photo', 'first_name', 'last_name', 'email', 'university_id', 'major', 'nim', 'semester', 'whatsapp_number', 'bio', 'stories[]', 'skills[]', 'interests[]', 'current_password', 'password', 'password_confirmation'] as $name) {
            $this->assertStringContainsString('name="' . $name . '"', $html, "input {$name} hilang");
        }
        foreach (['identitas', 'akademik', 'kontak', 'portofolio', 'keamanan'] as $section) {
            $this->assertStringContainsString('id="bagian-' . $section . '"', $html);
            $this->assertStringContainsString('href="#bagian-' . $section . '"', $html);
        }

        // dua form terpisah, metode dan tujuan sama seperti sebelumnya
        $this->assertSame(2, substr_count($html, 'name="_method" value="PUT"'));
        $this->assertStringContainsString('action="' . route('student.profile.update') . '" method="POST" enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('action="' . route('student.profile.password.update') . '" method="POST"', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_edit_form_without_lists_renders(): void
    {
        $student = $this->makeStudent('Gita', 'Permata', null, ['stories' => null, 'skills' => null, 'interests' => null]);
        $this->actingAs($student->user)->get('/student/profile/edit')->assertOk()->assertSee('Tambah cerita');
    }

    public function test_update_still_saves_and_redirects_to_profile(): void
    {
        $this->actingAs($this->student->user)->put('/student/profile', [
            'first_name' => 'Farah',
            'last_name' => 'Wibowo Putri',
            'email' => $this->student->user->email,
            'university_id' => $this->universityId,
            'major' => 'Teknik Lingkungan',
            'nim' => $this->student->nim,
            'semester' => 7,
            'whatsapp_number' => '081299887766',
            'bio' => 'Bio baru.',
            'stories' => ['Cerita baru.'],
            'skills' => ['GIS'],
            'interests' => [],
        ])->assertRedirect(route('student.profile.index'));

        $fresh = $this->student->fresh();
        $this->assertSame('Wibowo Putri', $fresh->last_name);
        $this->assertSame(['Cerita baru.'], $fresh->stories);
        $this->assertSame(['GIS'], $fresh->skills);
        $this->assertSame('Farah Wibowo Putri', $fresh->user->name);
    }

    public function test_wrong_current_password_returns_to_edit_with_error(): void
    {
        $this->actingAs($this->student->user)
            ->from('/student/profile/edit')
            ->put('/student/profile/password', ['current_password' => 'salah', 'password' => 'baru-12345', 'password_confirmation' => 'baru-12345'])
            ->assertRedirect('/student/profile/edit')
            ->assertSessionHasErrors('current_password');
        $this->assertAuthenticatedAs($this->student->user);
    }

    public function test_profile_has_identity_header_share_link_and_no_hero(): void
    {
        $html = $this->actingAs($this->student->user)->get('/student/profile')->assertOk()->getContent();
        $url = route('profile.public', $this->student->user->username);

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertMatchesRegularExpression('/<h1[^>]*>Farah Wibowo<\/h1>/', $html);
        $this->assertStringContainsString('data-initials="FW"', $html);
        $this->assertStringNotContainsString('Candra Hasanah', $html);
        $this->assertStringContainsString('data-sg-copy="' . $url . '"', $html);
        $this->assertStringContainsString('Salin tautan portofolio', $html);
        $this->assertStringContainsString('sg-btn--secondary', $html);

        foreach (['dashboard-student3.jpg', 'backdrop-blur', 'bg-gradient', 'Karsa'] as $banned) {
            $this->assertStringNotContainsString($banned, $html, "masih memuat {$banned}");
        }
        // pemilik melihat kontaknya sendiri
        $this->assertStringContainsString($this->student->user->email, $html);
    }

    public function test_empty_story_shows_honest_empty_state(): void
    {
        $student = $this->makeStudent('Hana', 'Lestari', null, ['stories' => []]);

        $this->actingAs($student->user)->get('/student/profile')->assertOk()
            ->assertSee('Belum ada cerita')
            ->assertSee(route('student.profile.edit') . '#bagian-portofolio', false)
            ->assertSee('Belum ada proyek selesai')
            ->assertSee(route('student.browse-problems.index'), false);

        auth()->logout();
        $this->get('/profile/' . $student->user->username)->assertOk()
            ->assertSee('Hana Lestari belum menulis cerita pengalaman.')
            ->assertSee('Belum ada proyek selesai yang ditampilkan.');
    }

    public function test_story_is_shown_as_reading_text(): void
    {
        $this->get('/profile/' . $this->student->user->username)->assertOk()
            ->assertSee('Saya belajar mendengarkan warga Desa Sukamaju.')
            ->assertSee('sg-prose', false);
    }

    public function test_completed_projects_show_institution_and_year_and_respect_visibility(): void
    {
        $this->makeCompletedProject(true, 'Air Bersih Desa Sukamaju');
        $this->makeCompletedProject(false, 'Proyek Tersembunyi');

        $this->actingAs($this->student->user)->get('/student/profile')->assertOk()
            ->assertSee('Air Bersih Desa Sukamaju')
            ->assertSee('Proyek Tersembunyi')
            ->assertSee('Dinas Kesehatan Kota Bandung', false)
            ->assertSee('Selesai Okt 2026')
            ->assertSee('role="switch"', false);

        auth()->logout();
        $this->get('/profile/' . $this->student->user->username)->assertOk()
            ->assertSee('Air Bersih Desa Sukamaju')
            ->assertDontSee('Proyek Tersembunyi')
            ->assertDontSee('role="switch"', false);
    }

    public function test_public_profile_has_meta_and_no_contact_for_guest(): void
    {
        $this->makeCompletedProject();
        $response = $this->get('/profile/' . $this->student->user->username)->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('<title>Portofolio Farah Wibowo — Student Go</title>', $html);
        $this->assertMatchesRegularExpression('/<meta name="description" content="Portofolio KKN Farah Wibowo, Teknik Informatika di Institut Teknologi Bandung\. 1 proyek selesai\./', $html);
        $this->assertStringContainsString('<meta property="og:title" content="Portofolio Farah Wibowo">', $html);
        $this->assertStringContainsString('<meta property="og:url" content="' . route('profile.public', $this->student->user->username) . '">', $html);
        $this->assertStringNotContainsString('og:image', $html);

        $this->assertStringNotContainsString($this->student->user->email, $html);
        $this->assertStringNotContainsString('81299887766', $html);
        $this->assertStringContainsString('Email dan WhatsApp hanya terlihat oleh koneksi yang sudah diterima.', $html);
        $this->assertStringNotContainsString('dashboard-student3.jpg', $html);
        $this->assertStringNotContainsString('data-sg-copy', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_initials_follow_canonical_name_without_photo_placeholder(): void
    {
        $html = $this->get('/profile/' . $this->student->user->username)->getContent();
        $this->assertStringContainsString('data-initials="' . $this->student->initials . '"', $html);
        $this->assertStringNotContainsString('ui-avatars.com', $html);
    }
}
