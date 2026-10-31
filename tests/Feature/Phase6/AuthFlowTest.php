<?php

namespace Tests\Feature\Phase6;

use App\Jobs\ValidateInstitutionDocumentsJob;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 6: perilaku backend autentikasi yang harus tetap sama setelah halaman auth didesain ulang.
 * masuk (email/username, password salah, akun tidak ada, wajib isi, ingat saya, regenerasi sesi, redirect per peran),
 * keluar, middleware guest/auth/peran, tiga registrasi (json seperti yang dipakai halaman), lupa/reset password.
 */
class AuthFlowTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private const PASSWORD = 'Rahasia#2026';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        Notification::fake();
        Mail::fake();
        Queue::fake();
        Storage::fake('public');
    }

    private function student(): Student
    {
        return $this->makeStudent('Sari', 'Wulandari', null, [], ['password' => Hash::make(self::PASSWORD)]);
    }

    // ── masuk ──

    public function test_login_redirects_each_role_to_its_dashboard(): void
    {
        $student = $this->student();
        $institution = $this->makeInstitution();
        $institution->user->update(['password' => Hash::make(self::PASSWORD)]);
        $company = $this->makeCompany();
        $company->user->update(['password' => Hash::make(self::PASSWORD)]);

        foreach ([[$student->user, 'student.dashboard'], [$institution->user, 'institution.dashboard'], [$company->user, 'company.dashboard']] as [$user, $route]) {
            $this->post(route('login'), ['email' => $user->email, 'password' => self::PASSWORD])
                ->assertRedirect(route($route));
            $this->assertAuthenticatedAs($user);
            $this->post(route('logout'));
        }
    }

    public function test_login_accepts_username_and_regenerates_the_session(): void
    {
        $student = $this->student();
        $this->get(route('login'));
        $before = session()->getId();

        $this->post(route('login'), ['email' => $student->user->username, 'password' => self::PASSWORD])
            ->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($student->user);
        $this->assertNotSame($before, session()->getId());
    }

    public function test_wrong_password_and_unknown_account_get_the_same_message(): void
    {
        $student = $this->student();

        $this->from(route('login'))->post(route('login'), ['email' => $student->user->email, 'password' => 'Salah#2026'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'email atau password salah.']);
        $this->assertGuest();

        $this->from(route('login'))->post(route('login'), ['email' => 'tidak.ada@contoh.test', 'password' => 'Salah#2026'])
            ->assertSessionHasErrors(['email' => 'email atau password salah.']);
        $this->assertGuest();
    }

    public function test_login_requires_both_fields_and_keeps_old_input(): void
    {
        $this->from(route('login'))->post(route('login'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'email atau username wajib diisi', 'password' => 'password wajib diisi']);

        $this->from(route('login'))->post(route('login'), ['email' => 'sari@contoh.test', 'password' => '']);
        $html = $this->get(route('login'))->assertOk()->getContent();
        $this->assertStringContainsString('value="sari@contoh.test"', $html);
        $this->assertStringContainsString('password wajib diisi', $html);
        $this->assertMatchesRegularExpression('/id="password"[^>]*aria-invalid="true"/s', $html);
    }

    public function test_remember_me_sets_the_recaller_cookie_only_when_checked(): void
    {
        $student = $this->student();
        $recaller = Auth::guard()->getRecallerName();

        $this->post(route('login'), ['email' => $student->user->email, 'password' => self::PASSWORD])
            ->assertCookieMissing($recaller);
        $this->post(route('logout'));

        $this->post(route('login'), ['email' => $student->user->email, 'password' => self::PASSWORD, 'remember' => '1'])
            ->assertCookie($recaller);
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $student = $this->student();
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), ['email' => $student->user->email, 'password' => 'Salah#2026']);
        }

        $this->post(route('login'), ['email' => $student->user->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ── keluar, sesi dan akses ──

    public function test_logout_ends_the_session_and_regenerates_the_token(): void
    {
        $student = $this->student();
        $this->post(route('login'), ['email' => $student->user->email, 'password' => self::PASSWORD]);
        $token = session()->token();

        $this->post(route('logout'))->assertRedirect(route('home'))->assertSessionHas('success');

        $this->assertGuest();
        $this->assertNotSame($token, session()->token());
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    public function test_logout_requires_post_and_a_session(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->get('/logout')->assertStatus(405);
    }

    public function test_guest_pages_redirect_signed_in_users_away(): void
    {
        $student = $this->student();
        $this->actingAs($student->user);

        foreach (['login', 'register', 'register.student', 'register.institution', 'register.company', 'password.request'] as $name) {
            $this->get(route($name))->assertRedirect();
        }
    }

    public function test_protected_and_cross_role_pages_follow_the_existing_rules(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('company.dashboard'))->assertRedirect(route('login'));

        $student = $this->student();
        $this->actingAs($student->user)
            ->get(route('institution.dashboard'))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('error', 'anda tidak memiliki akses ke halaman ini');
        $this->get(route('company.dashboard'))->assertRedirect(route('student.dashboard'));
        $this->get('/admin/dashboard')->assertStatus(403);
    }

    // ── registrasi (json, seperti dikirim halaman) ──

    private function studentPayload(array $over = []): array
    {
        return array_merge([
            'first_name' => 'Dimas', 'last_name' => 'Prasetyo', 'email' => 'dimas.prasetyo@contoh.test',
            'whatsapp_number' => '081234567890', 'university_id' => $this->universityId, 'major' => 'Teknik Sipil',
            'nim' => '15321999', 'semester' => '5', 'username' => 'dimasprasetyo',
            'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD, 'terms' => 'on',
        ], $over);
    }

    public function test_student_registration_creates_a_hashed_student_account_and_signs_in(): void
    {
        $this->postJson(route('register.student.submit'), $this->studentPayload(['user_type' => 'admin']))
            ->assertOk()
            ->assertJson(['success' => true, 'redirect_url' => route('student.dashboard')]);

        $user = User::where('email', 'dimas.prasetyo@contoh.test')->firstOrFail();
        $this->assertSame('student', $user->user_type, 'peran tidak boleh diambil dari input');
        $this->assertNotSame(self::PASSWORD, $user->password);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        $this->assertSame('15321999', $user->student->nim);
        $this->assertAuthenticatedAs($user);
    }

    public function test_student_registration_validation_errors(): void
    {
        $taken = $this->student();

        $this->postJson(route('register.student.submit'), $this->studentPayload(['email' => $taken->user->email]))
            ->assertStatus(422)->assertJsonValidationErrors(['email' => 'Email Sudah Terdaftar']);
        $this->postJson(route('register.student.submit'), $this->studentPayload(['password_confirmation' => 'Lain#2026']))
            ->assertStatus(422)->assertJsonValidationErrors(['password' => 'Konfirmasi Password Tidak Cocok']);
        $this->postJson(route('register.student.submit'), $this->studentPayload(['password' => 'lemah', 'password_confirmation' => 'lemah']))
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->postJson(route('register.student.submit'), $this->studentPayload(['first_name' => '', 'nim' => '']))
            ->assertStatus(422)->assertJsonValidationErrors(['first_name', 'nim']);

        $this->assertSame(1, User::count());
        $this->assertGuest();
    }

    public function test_student_step_validation_endpoint_is_unchanged(): void
    {
        $this->postJson(route('validation.student.step'), ['step' => 1])
            ->assertStatus(422)->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'whatsapp_number']);
        $this->postJson(route('validation.student.step'), ['step' => 1, 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@contoh.test', 'whatsapp_number' => '081234567890'])
            ->assertOk();
    }

    public function test_institution_registration_keeps_its_review_flow(): void
    {
        $payload = [
            'institution_name' => 'pemerintah desa sukamaju', 'institution_type' => 'pemerintah_desa', 'official_email' => 'Pemdes@Sukamaju.test',
            'address' => 'Jl. Raya Sukamaju No. 12', 'province_id' => $this->provinceId, 'regency_id' => $this->regencyId,
            'pic_name' => 'budi santoso', 'pic_position' => 'Sekretaris Desa', 'phone_number' => '0812 3456 7890',
            'username' => 'desasukamaju', 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD,
            'verification_document' => UploadedFile::fake()->create('sk.pdf', 100, 'application/pdf'),
            'ktp' => UploadedFile::fake()->image('ktp.jpg'), 'npwp' => UploadedFile::fake()->image('npwp.png'),
            'user_type' => 'admin',
        ];

        $this->postJson(route('register.institution.submit'), $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.redirect_url', route('home'));

        $user = User::where('email', 'pemdes@sukamaju.test')->firstOrFail();
        $this->assertSame('institution', $user->user_type);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        $this->assertFalse((bool) $user->institution->is_verified);
        $this->assertGuest();   // tidak login otomatis: menunggu verifikasi
        Queue::assertPushed(ValidateInstitutionDocumentsJob::class);

        // dokumen wajib
        $this->postJson(route('register.institution.submit'), array_merge($payload, ['official_email' => 'lain@contoh.test', 'username' => 'lainlain', 'ktp' => null, 'npwp' => null, 'verification_document' => null]))
            ->assertStatus(422)->assertJsonValidationErrors(['ktp', 'npwp', 'verification_document']);
    }

    public function test_company_registration_creates_a_company_account_without_signing_in(): void
    {
        $payload = [
            'company_name' => 'cv tani makmur', 'industry' => 'Pendidikan', 'company_size' => '1-10',
            'email' => 'info@tanimakmur.test', 'pic_name' => 'Siti Rahmawati', 'pic_position' => 'Manajer SDM',
            'username' => 'tanimakmur', 'password' => self::PASSWORD, 'password_confirmation' => self::PASSWORD,
            'user_type' => 'admin',
        ];

        $this->withHeader('X-Requested-With', 'XMLHttpRequest')->postJson(route('register.company.submit'), $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.redirect_url', route('home'));

        $user = User::where('email', 'info@tanimakmur.test')->firstOrFail();
        $this->assertSame('company', $user->user_type);
        $this->assertSame('pending_verification', $user->company->verification_status);
        $this->assertGuest();

        $this->postJson(route('register.company.submit'), array_merge($payload, ['username' => 'lainlain']))
            ->assertStatus(422)->assertJsonValidationErrors(['email' => 'email sudah terdaftar']);
    }

    // ── lupa dan reset password ──

    public function test_forgot_password_sends_the_reset_notification(): void
    {
        $student = $this->student();

        $this->from(route('password.request'))->post(route('password.email'), ['email' => $student->user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('success');
        Notification::assertSentTo($student->user, ResetPassword::class);

        $this->from(route('password.request'))->post(route('password.email'), ['email' => 'bukan-email'])
            ->assertSessionHasErrors(['email' => 'format email tidak valid']);
    }

    public function test_reset_password_changes_the_password_with_a_valid_token(): void
    {
        $student = $this->student();
        $token = Password::createToken($student->user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $student->user->email]))
            ->assertOk()
            ->assertSee('value="'.$student->user->email.'"', false)
            ->assertSee('name="token" value="'.$token.'"', false);

        $this->post(route('password.update'), [
            'token' => $token, 'email' => $student->user->email,
            'password' => 'BaruSekali#2026', 'password_confirmation' => 'BaruSekali#2026',
        ])->assertRedirect(route('login'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('BaruSekali#2026', $student->user->fresh()->password));

        $this->from(route('login'))->post(route('password.update'), [
            'token' => 'token-salah', 'email' => $student->user->email,
            'password' => 'BaruLagi#2026', 'password_confirmation' => 'BaruLagi#2026',
        ])->assertSessionHasErrors('email');
    }

    public function test_verify_email_page_renders_for_a_signed_in_unverified_user(): void
    {
        $student = $this->makeStudent('Rina', 'Kartika', null, [], ['email_verified_at' => null]);

        $html = $this->actingAs($student->user)->get(route('verification.notice'))->assertOk()->getContent();
        $this->assertStringContainsString('Verifikasi email Anda', $html);
        $this->assertStringContainsString($student->user->email, $html);
        $this->assertStringContainsString('action="'.route('verification.resend').'"', $html);
        $this->assertStringContainsString('action="'.route('logout').'"', $html);
    }
}
