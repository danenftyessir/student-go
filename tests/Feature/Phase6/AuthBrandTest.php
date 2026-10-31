<?php

namespace Tests\Feature\Phase6;

use App\Mail\InstitutionApproved;
use App\Mail\InstitutionRegistered;
use App\Mail\InstitutionRejected;
use App\Mail\VerifyEmailMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 6 (D-40): email autentikasi/verifikasi dan pesan flash auth memakai nama Student Go, bukan nama lama.
 */
class AuthBrandTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private const OLD = '/karya untuk bangsa/i';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        Notification::fake();
    }

    public function test_institution_emails_use_student_go(): void
    {
        $institution = $this->makeInstitution();
        // atribut hasil validasi ai yang dibaca template (tidak disimpan)
        $institution->forceFill(['ai_validation_score' => 87.5, 'ai_validated_at' => now(), 'ai_validation_notes' => 'Dokumen kurang jelas.']);

        foreach ([new InstitutionRegistered($institution), new InstitutionApproved($institution), new InstitutionRejected($institution)] as $mail) {
            $subject = $mail->envelope()->subject;
            // teks yang dibaca penerima (tautan memakai APP_URL dari env, bukan copy)
            $text = strip_tags($mail->render());
            $this->assertStringEndsWith('| Student Go', $subject, $mail::class);
            $this->assertDoesNotMatchRegularExpression(self::OLD, $subject.$text, $mail::class);
            $this->assertStringContainsString('Student Go', $text, $mail::class);
        }
    }

    public function test_verification_email_subject_uses_student_go(): void
    {
        $student = $this->makeStudent('Rina', 'Kartika');

        $this->assertSame('verifikasi email anda - Student Go', (new VerifyEmailMail($student->user))->envelope()->subject);
    }

    public function test_auth_flash_messages_use_student_go(): void
    {
        foreach (['app/Http/Controllers/Auth/RegisterController.php', 'app/Http/Controllers/Auth/EmailVerificationController.php'] as $file) {
            $this->assertDoesNotMatchRegularExpression(self::OLD, file_get_contents(base_path($file)), $file);
        }

        $student = $this->makeStudent('Rina', 'Kartika', null, [], ['email_verified_at' => null]);
        $this->actingAs($student->user)
            ->get(route('verification.verify', ['id' => $student->user->id, 'hash' => sha1($student->user->email)]))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('success', 'email berhasil diverifikasi! selamat bergabung di Student Go');
    }
}
