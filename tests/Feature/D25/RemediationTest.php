<?php

namespace Tests\Feature\D25;

use App\Services\PortfolioService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * remediasi D-25: test yang tidak bergantung pada sqlite.
 * di sqlite id 'abc' tidak memicu QueryException, jadi 404 di sana
 * belum membuktikan constraint rute; di sini rute dicocokkan langsung.
 */
class RemediationTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    public function test_get_chatbot_create_and_refresh_never_persist_a_conversation(): void
    {
        $student = $this->makeStudent('Hana', 'Kamila');
        $this->actingAs($student->user);

        $this->get('/student/chatbot/create')->assertRedirect(route('student.chatbot.index'));
        $this->get('/student/chatbot/create')->assertRedirect(route('student.chatbot.index'));
        $this->get('/student/chatbot')->assertOk();
        $this->get('/student/chatbot')->assertOk();

        $this->assertSame(0, DB::table('chat_conversations')->count());
    }

    public function test_non_numeric_ids_never_reach_a_controller(): void
    {
        foreach ([
            '/student/repository/abc',
            '/student/repository/abc/download',
            '/institution/reviews/create',
            '/institution/reviews/create/abc',
            '/institution/reviews/abc/edit',
        ] as $uri) {
            try {
                $route = Route::getRoutes()->match(Request::create($uri, 'GET'));
                $this->fail($uri . ' cocok dengan rute ' . $route->getName());
            } catch (NotFoundHttpException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_numeric_ids_still_match_the_same_routes(): void
    {
        $this->assertSame('student.repository.show', Route::getRoutes()->match(Request::create('/student/repository/12', 'GET'))->getName());
        $this->assertSame('student.repository.download', Route::getRoutes()->match(Request::create('/student/repository/12/download', 'GET'))->getName());
        $this->assertSame('institution.reviews.create', Route::getRoutes()->match(Request::create('/institution/reviews/create/5', 'GET'))->getName());
        $this->assertSame('institution.reviews.show', Route::getRoutes()->match(Request::create('/institution/reviews/5', 'GET'))->getName());
    }

    public function test_safe_error_message_hides_details_unless_debug(): void
    {
        $e = $this->pgsqlQueryException();

        config(['app.debug' => false]);
        $this->assertSame('Terjadi kesalahan.', safe_error_message('Terjadi kesalahan: ', $e));
        $this->assertSame('Gagal menolak institution.', safe_error_message('Gagal menolak institution. Error: ', $e));
        $this->assertSame(
            'Terjadi kesalahan saat registrasi. Silakan coba lagi.',
            safe_error_message('Terjadi kesalahan saat registrasi. Silakan coba lagi. Error: ', $e)
        );

        config(['app.debug' => true]);
        $this->assertStringContainsString('db.rahasia.internal', safe_error_message('Terjadi kesalahan: ', $e));
    }

    public function test_caught_query_exception_is_not_returned_to_the_user_without_debug(): void
    {
        config(['app.debug' => false]);
        $student = $this->makeStudent('Hana', 'Kamila');
        $institution = $this->makeInstitution();
        $problemId = $this->makeOpenProblems($institution->id, 1)[0];
        $this->makeProject($student, $institution->id, $problemId);
        $projectId = DB::table('projects')->value('id');

        $this->mock(PortfolioService::class, function ($mock) {
            $mock->shouldReceive('toggleProjectVisibility')->andThrow($this->pgsqlQueryException());
        });

        $response = $this->actingAs($student->user)
            ->postJson(route('student.profile.project.toggle-visibility', $projectId));

        $response->assertStatus(500)->assertJson(['success' => false, 'message' => 'Gagal mengubah visibility.']);
        $this->assertStringNotContainsString('db.rahasia.internal', $response->getContent());
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
    }

    public function test_no_controller_returns_a_raw_exception_message(): void
    {
        $this->assertSame([], self::rawExceptionMessages(app_path('Http/Controllers')));
    }

    /**
     * pernyataan yang memakai ->getMessage() di luar log, throw atau pengecekan app.debug
     */
    public static function rawExceptionMessages(string $directory): array
    {
        $leaks = [];
        foreach (File::allFiles($directory) as $file) {
            foreach (explode(';', $file->getContents()) as $statement) {
                if (str_contains($statement, '->getMessage()')
                    && ! preg_match('/Log::|logger\(|report\(|throw new|app\.debug/', $statement)) {
                    $leaks[] = $file->getRelativePathname();
                }
            }
        }

        return $leaks;
    }

    /**
     * QueryException seperti dari pooler postgres: pesannya memuat host, port, database dan SQL
     */
    private function pgsqlQueryException(): QueryException
    {
        return new QueryException(
            'pgsql',
            'update projects set is_portfolio_visible = ? where id = ?',
            [true, 1],
            new \PDOException('SQLSTATE[08006] koneksi gagal'),
            ['driver' => 'pgsql', 'host' => 'db.rahasia.internal', 'port' => 6543, 'database' => 'postgres'],
        );
    }
}
