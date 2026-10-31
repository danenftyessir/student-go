<?php

namespace Tests\Feature\Phase1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * J13 / D-25: GET tidak boleh membuat record, id non-angka = 404,
 * dan halaman error tidak membocorkan detail di luar mode local
 */
class HttpDefectsTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    public function test_get_chatbot_create_does_not_persist_a_conversation(): void
    {
        $student = $this->makeStudent('Hana', 'Kamila');

        $this->actingAs($student->user)->get('/student/chatbot/create')
            ->assertRedirect(route('student.chatbot.index'));

        $this->assertSame(0, DB::table('chat_conversations')->count());
    }

    public function test_post_chatbot_create_creates_one_conversation_and_opens_it(): void
    {
        $student = $this->makeStudent('Hana', 'Kamila');

        $response = $this->actingAs($student->user)->post('/student/chatbot/create');

        $this->assertSame(1, DB::table('chat_conversations')->where('user_id', $student->user_id)->count());
        $id = DB::table('chat_conversations')->value('id');
        $response->assertRedirect(route('student.chatbot.show', $id));
    }

    public function test_chatbot_new_conversation_buttons_post_instead_of_get(): void
    {
        $student = $this->makeStudent('Hana', 'Kamila');

        $html = $this->actingAs($student->user)->get('/student/chatbot')->assertOk()->getContent();

        $this->assertStringNotContainsString('href="' . route('student.chatbot.create') . '"', $html);
        $this->assertStringContainsString('action="' . route('student.chatbot.store') . '"', $html);
    }

    public function test_non_numeric_ids_return_404(): void
    {
        $student = $this->makeStudent('Hana', 'Kamila');
        $institution = $this->makeInstitution();

        $this->actingAs($student->user)->get('/student/repository/abc')->assertNotFound();
        $this->actingAs($student->user)->get('/student/browse-problems/abc')->assertNotFound();
        $this->actingAs($student->user)->get('/student/chatbot/abc')->assertNotFound();
        $this->actingAs($institution->user)->get('/institution/reviews/create')->assertNotFound();
        $this->get('/institution/abc')->assertNotFound();
    }

    public function test_numeric_ids_still_resolve(): void
    {
        $student = $this->makeStudent('Hana', 'Kamila');
        $this->actingAs($student->user)->get('/student/browse-problems/999')->assertNotFound();
        $this->assertSame(
            url('/student/repository/12'),
            route('student.repository.show', 12)
        );
    }

    public function test_debug_is_forced_off_outside_local_environment(): void
    {
        $this->assertFalse($this->evaluateDebug('production', 'true'));
        $this->assertFalse($this->evaluateDebug('staging', 'true'));
        $this->assertTrue($this->evaluateDebug('local', 'true'));
        $this->assertFalse($this->evaluateDebug('local', 'false'));
    }

    public function test_error_page_without_debug_has_no_stack_trace(): void
    {
        config(['app.debug' => false]);
        Route::get('/__phase1_boom', fn () => throw new \RuntimeException('SQLSTATE rahasia host=db.internal'));

        $response = $this->get('/__phase1_boom');
        $response->assertStatus(500);
        $response->assertDontSee('SQLSTATE');
        $response->assertDontSee('db.internal');
        $response->assertDontSee('Stack trace');
    }

    /**
     * evaluasi config/app.php dengan env tertentu
     */
    private function evaluateDebug(string $env, string $debug): bool
    {
        $backup = [$_ENV['APP_ENV'] ?? null, $_SERVER['APP_ENV'] ?? null, $_ENV['APP_DEBUG'] ?? null, $_SERVER['APP_DEBUG'] ?? null];
        $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = $env;
        $_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] = $debug;

        try {
            $config = require base_path('config/app.php');

            return $config['debug'];
        } finally {
            [$_ENV['APP_ENV'], $_SERVER['APP_ENV'], $_ENV['APP_DEBUG'], $_SERVER['APP_DEBUG']] = $backup;
            foreach (['APP_ENV', 'APP_DEBUG'] as $key) {
                if ($_ENV[$key] === null) {
                    unset($_ENV[$key]);
                }
                if ($_SERVER[$key] === null) {
                    unset($_SERVER[$key]);
                }
            }
        }
    }
}
