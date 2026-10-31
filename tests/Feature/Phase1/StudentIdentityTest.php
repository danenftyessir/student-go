<?php

namespace Tests\Feature\Phase1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * J1 / J5 / D-03: nama mahasiswa berasal dari students.first_name + last_name
 * di semua halaman, walaupun users.name di database berbeda (data seed lama)
 */
class StudentIdentityTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    private $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        // meniru data seed: users.name berbeda dari nama di tabel students
        $this->student = $this->makeStudent('Farah', 'Wibowo', 'Candra Hasanah');
    }

    public function test_single_accessor_gives_name_and_initials(): void
    {
        $this->assertSame('Farah Wibowo', $this->student->display_name);
        $this->assertSame('FW', $this->student->initials);
        $this->assertSame('Farah Wibowo', $this->student->user->display_name);
        $this->assertSame('FW', $this->student->user->initials);
    }

    public function test_initials_handle_single_and_empty_names(): void
    {
        $mono = $this->makeStudent('Sukarno', '');
        $this->assertSame('Sukarno', $mono->display_name);
        $this->assertSame('S', $mono->initials);
    }

    public function test_non_student_display_name_falls_back_to_users_name(): void
    {
        $company = $this->makeCompany();
        $this->assertSame($company->user->name, $company->user->display_name);
    }

    public function test_nav_and_dashboard_use_canonical_name(): void
    {
        $response = $this->actingAs($this->student->user)->get('/student/dashboard');
        $response->assertOk();
        $response->assertSee('Farah Wibowo');
        $response->assertDontSee('Candra Hasanah');
    }

    public function test_profile_pages_use_canonical_name(): void
    {
        foreach (['/student/profile', '/profile/' . $this->student->user->username] as $url) {
            $response = $this->actingAs($this->student->user)->get($url);
            $response->assertOk();
            $response->assertSee('Farah Wibowo');
            $response->assertDontSee('Candra Hasanah');
        }

        $guest = $this->app->make('auth')->guard()->logout();
        $response = $this->get('/profile/' . $this->student->user->username);
        $response->assertOk();
        $response->assertSee('Farah Wibowo');
        $response->assertDontSee('Candra Hasanah');
    }

    public function test_edit_form_loads_stored_first_and_last_name(): void
    {
        $response = $this->actingAs($this->student->user)->get('/student/profile/edit');
        $response->assertOk();
        $response->assertSee('name="first_name"', false);
        $this->assertMatchesRegularExpression('/name="first_name"\s+value="Farah"/', $response->getContent());
        $this->assertMatchesRegularExpression('/name="last_name"\s+value="Wibowo"/', $response->getContent());
    }

    public function test_friends_search_shows_and_finds_canonical_name(): void
    {
        $viewer = $this->makeStudent('Zaki', 'Viewer');

        $response = $this->actingAs($viewer->user)->get('/student/friends/search');
        $response->assertOk();
        $response->assertSee('Farah Wibowo');
        $response->assertDontSee('Candra Hasanah');

        // pencarian memakai nama yang terlihat, bukan users.name tersembunyi
        $found = $this->actingAs($viewer->user)->get('/student/friends/search?search=Farah')->viewData('results');
        $this->assertSame([$this->student->id], collect($found->items())->pluck('id')->all());
        $hidden = $this->actingAs($viewer->user)->get('/student/friends/search?search=Candra')->viewData('results');
        $this->assertSame(0, $hidden->total());
    }

    public function test_company_talents_use_canonical_name(): void
    {
        $company = $this->makeCompany();
        $institution = $this->makeInstitution();
        $problemIds = $this->makeOpenProblems($institution->id, 1);
        $this->makeProject($this->student, $institution->id, $problemIds[0]);

        $json = $this->actingAs($company->user)
            ->getJson('/company/talents', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->json('talents');

        $this->assertSame('Farah Wibowo', $json[0]['name']);
    }

    public function test_registration_and_update_keep_users_name_in_sync(): void
    {
        // nama users ikut nama di students setelah update profil
        $this->actingAs($this->student->user)->put('/student/profile', [
            'first_name' => 'Farah',
            'last_name' => 'Wibowo',
            'university_id' => $this->universityId,
            'major' => 'Teknik Informatika',
            'nim' => $this->student->nim,
            'semester' => 5,
            'phone' => $this->student->phone,
            'whatsapp_number' => $this->student->phone,
            'email' => $this->student->user->email,
            'username' => $this->student->user->username,
        ]);

        $this->assertSame('Farah Wibowo', $this->student->user->fresh()->name);
    }
}
