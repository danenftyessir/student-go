<?php

namespace Tests\Feature\Phase1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * J3 / D-04: email dan whatsapp mahasiswa di /profile/{username}
 * hanya dikirim ke pemilik dan koneksi yang sudah diterima
 */
class ContactPrivacyTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    private $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->owner = $this->makeStudent('Desi', 'Hartono', null, ['phone' => '+6281299887766']);
    }

    private function assertNoContact($response): void
    {
        $response->assertOk();
        $response->assertDontSee($this->owner->user->email);
        $response->assertDontSee('6281299887766');
        $response->assertDontSee('81299887766');
    }

    private function assertContact($response): void
    {
        $response->assertOk();
        $response->assertSee($this->owner->user->email);
        $response->assertSee('+6281299887766');
    }

    public function test_guest_receives_no_email_or_whatsapp(): void
    {
        $this->assertNoContact($this->get('/profile/' . $this->owner->user->username));
    }

    public function test_unconnected_student_receives_no_contact(): void
    {
        $other = $this->makeStudent('Eko', 'Saputra');
        $this->assertNoContact($this->actingAs($other->user)->get('/profile/' . $this->owner->user->username));
    }

    public function test_pending_connection_receives_no_contact(): void
    {
        $other = $this->makeStudent('Eko', 'Saputra');
        $this->makeFriendship($other, $this->owner, 'pending');
        $this->assertNoContact($this->actingAs($other->user)->get('/profile/' . $this->owner->user->username));
    }

    public function test_company_recruiter_receives_no_contact(): void
    {
        $company = $this->makeCompany();
        $this->assertNoContact($this->actingAs($company->user)->get('/profile/' . $this->owner->user->username));
    }

    public function test_institution_receives_no_contact(): void
    {
        $institution = $this->makeInstitution();
        $this->assertNoContact($this->actingAs($institution->user)->get('/profile/' . $this->owner->user->username));
    }

    public function test_owner_sees_own_contact(): void
    {
        $this->assertContact($this->actingAs($this->owner->user)->get('/profile/' . $this->owner->user->username));
    }

    public function test_accepted_connection_sees_contact_in_both_directions(): void
    {
        $requester = $this->makeStudent('Fajar', 'Nugroho');
        $this->makeFriendship($requester, $this->owner, 'accepted');
        $this->assertContact($this->actingAs($requester->user)->get('/profile/' . $this->owner->user->username));

        $receiver = $this->makeStudent('Gita', 'Permata');
        $this->makeFriendship($this->owner, $receiver, 'accepted');
        $this->assertContact($this->actingAs($receiver->user)->get('/profile/' . $this->owner->user->username));
    }

    public function test_friends_profile_redirect_still_points_to_public_profile(): void
    {
        $other = $this->makeStudent('Eko', 'Saputra');
        $this->actingAs($other->user)
            ->get('/student/friends/profile/' . $this->owner->id)
            ->assertRedirect(route('profile.public', $this->owner->user->username));
    }

    public function test_friends_search_json_does_not_leak_contact(): void
    {
        $other = $this->makeStudent('Eko', 'Saputra');

        $response = $this->actingAs($other->user)
            ->getJson('/student/friends/search', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $body = $response->getContent();
        $this->assertStringContainsString('Desi', $body);
        $this->assertStringNotContainsString($this->owner->user->email, $body);
        $this->assertStringNotContainsString('81299887766', $body);
    }
}
