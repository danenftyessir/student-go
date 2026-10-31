<?php

namespace Tests\Feature\Phase26;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * phase 26: halaman galat bermerek. rute uji hanya didaftarkan di dalam test (tidak ada di routes/).
 */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    // pola halaman galat: merek, bahasa, kode, judul, kembali + beranda + kontak
    private function assertBrandedErrorPage($response, int $code, string $title): void
    {
        $response->assertStatus($code)
            ->assertSee('<html lang="id">', false)
            ->assertSee($title.' — Student Go</title>', false)
            ->assertSee('student-go-logo.svg', false)
            ->assertSee('<h1 id="sg-error-title" class="sg-error__title">'.$title.'</h1>', false)
            ->assertSee('Kembali')
            ->assertSee('Ke beranda')
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('href="'.route('contact').'"', false);

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    // tidak ada detail teknis saat debug mati
    private function assertNoTechnicalDetail($response): void
    {
        foreach (['Exception', 'SQLSTATE', 'Stack trace', 'vendor', base_path(), 'App\\', 'Unauthorized', 'Whoops'] as $leak) {
            $response->assertDontSee($leak, false);
        }
    }

    public function test_unknown_url_renders_branded_404(): void
    {
        $response = $this->get('/zz-not-a-route');

        $this->assertBrandedErrorPage($response, 404, 'Halaman tidak ditemukan');
        $this->assertNoTechnicalDetail($response);
    }

    public function test_non_admin_on_admin_dashboard_gets_branded_403_with_indonesian_message(): void
    {
        $student = User::factory()->create(['user_type' => 'student']);

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $this->assertBrandedErrorPage($response, 403, 'Akses tidak diizinkan');
        $response->assertSee('Hanya admin yang dapat mengakses halaman ini.');
        $this->assertNoTechnicalDetail($response);
    }

    public function test_admin_dashboard_still_redirects_guests_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_expired_csrf_token_renders_branded_419(): void
    {
        Route::middleware('web')->post('/__phase26/csrf', fn () => throw new TokenMismatchException('CSRF token mismatch.'));

        $response = $this->post('/__phase26/csrf');

        $this->assertBrandedErrorPage($response, 419, 'Sesi Anda sudah berakhir');
        $response->assertDontSee('CSRF token mismatch');
    }

    public function test_server_error_renders_standalone_500_without_internal_detail(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/__phase26/boom', fn () => throw new \RuntimeException('SQLSTATE[08006] host=db.internal user=postgres '.base_path()));

        $response = $this->get('/__phase26/boom');

        $this->assertBrandedErrorPage($response, 500, 'Terjadi kesalahan di server');
        $this->assertNoTechnicalDetail($response);
        $response->assertDontSee('db.internal')->assertDontSee('postgres');
        // templat mandiri: tanpa navbar, csrf, atau bundel js
        $response->assertDontSee('csrf-token', false)->assertDontSee('sg-navbar', false)->assertDontSee('alpinejs', false);
    }

    public function test_500_renders_when_the_database_is_unreachable(): void
    {
        config(['app.debug' => false]);
        // koneksi terpisah ke berkas yang tidak ada (koneksi test tetap utuh), lalu biarkan rute memicu query
        config(['database.connections.phase26_down' => ['driver' => 'sqlite', 'database' => base_path('__phase26_missing__/none.sqlite'), 'prefix' => '']]);
        Route::get('/__phase26/db-down', fn () => DB::connection('phase26_down')->select('select 1'));

        $response = $this->get('/__phase26/db-down');

        $this->assertBrandedErrorPage($response, 500, 'Terjadi kesalahan di server');
        $this->assertNoTechnicalDetail($response);
    }

    public function test_500_and_503_views_run_no_query(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $html500 = view('errors.500')->render();
        $html503 = view('errors.503')->render();

        $this->assertSame(0, $queries);
        $this->assertStringContainsString('Terjadi kesalahan di server', $html500);
        $this->assertStringContainsString('Student Go sedang dalam pemeliharaan', $html503);
    }

    public function test_service_unavailable_renders_branded_503(): void
    {
        Route::get('/__phase26/down', fn () => abort(503));

        $response = $this->get('/__phase26/down');

        $this->assertBrandedErrorPage($response, 503, 'Student Go sedang dalam pemeliharaan');
        $this->assertNoTechnicalDetail($response);
    }

    public function test_other_4xx_codes_use_the_branded_fallback_without_changing_status(): void
    {
        // /about hanya GET: POST tetap 405
        $response = $this->post('/about');

        $this->assertBrandedErrorPage($response, 405, 'Permintaan tidak dapat diproses');
        $response->assertSee('405');
    }

    public function test_json_requests_still_get_json_errors(): void
    {
        $this->getJson('/zz-not-a-route')->assertStatus(404)->assertJsonStructure(['message']);
    }
}
