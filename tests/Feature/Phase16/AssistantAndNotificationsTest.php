<?php

namespace Tests\Feature\Phase16;

use App\Models\Student;
use App\Support\NotificationTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 16: SAGO (dua kolom, satu h1, ikon kirim standar, tanpa GET yang membuat percakapan) dan kotak masuk
 * notifikasi (satu template untuk tiga peran, ikon menurut tipe, filter berlabel, aksi dan endpoint tetap).
 */
class AssistantAndNotificationsTest extends TestCase
{
    use BuildsPhase1Data;
    use RefreshDatabase;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->student = $this->makeStudent('Rani', 'Lestari');
    }

    private function main(string $html): string
    {
        preg_match('#<main[^>]*>(.*)</main>#s', $html, $m);

        return $m[1] ?? '';
    }

    private function conversation(int $userId, ?int $studentId, string $title, array $attrs = []): int
    {
        return DB::table('chat_conversations')->insertGetId(array_merge([
            'user_id' => $userId,
            'student_id' => $studentId,
            'title' => $title,
            'status' => 'active',
            'message_count' => 0,
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    private function message(int $conversationId, string $role, string $content): void
    {
        DB::table('chat_messages')->insert([
            'conversation_id' => $conversationId, 'role' => $role, 'content' => $content,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function notify(int $userId, string $type, string $title, array $attrs = []): int
    {
        return DB::table('notifications')->insertGetId(array_merge([
            'user_id' => $userId, 'type' => $type, 'title' => $title, 'message' => 'Pesan untuk ' . $type,
            'is_read' => false, 'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    // ── SAGO ────────────────────────────────────────────────────────────

    public function test_chat_list_is_two_columns_named_sago_without_hero(): void
    {
        $this->conversation($this->student->user_id, $this->student->id, 'Program bank sampah di Desa Sukamaju');

        $html = $this->actingAs($this->student->user)->get('/student/chatbot')->assertOk()->getContent();
        $main = $this->main($html);

        $this->assertSame(1, substr_count($main, '<h1'));
        $this->assertStringContainsString('<h1>SAGO</h1>', $main);
        $this->assertStringContainsString('Student Assistant for Guidance &amp; Opportunities', $main);
        $this->assertStringContainsString('sg-chat__layout', $main);
        $this->assertStringContainsString('aria-labelledby="chat-history-title"', $main);
        $this->assertStringContainsString('Program bank sampah di Desa Sukamaju', $main);
        // tanpa foto hero, tanpa font CDN
        $this->assertStringNotContainsString('deal-work-together', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString('<title>SAGO — Student Go</title>', $html);
    }

    public function test_new_conversation_is_only_a_post_form_and_viewing_never_creates_one(): void
    {
        $this->actingAs($this->student->user);
        $html = $this->get('/student/chatbot')->assertOk()->getContent();

        $this->assertStringNotContainsString('href="' . route('student.chatbot.create') . '"', $html);
        $this->assertSame(2, substr_count($html, 'action="' . route('student.chatbot.store') . '"'));
        $this->assertMatchesRegularExpression('#action="' . preg_quote(route('student.chatbot.store'), '#') . '">\s*<input type="hidden" name="_token"#', $html);

        $this->get('/student/chatbot');
        $this->get('/student/chatbot/create')->assertRedirect(route('student.chatbot.index'));
        $this->assertSame(0, DB::table('chat_conversations')->count());

        $id = $this->conversation($this->student->user_id, $this->student->id, 'Percakapan Baru');
        $this->get('/student/chatbot/' . $id)->assertOk();
        $this->get('/student/chatbot/' . $id)->assertOk();
        $this->assertSame(1, DB::table('chat_conversations')->count());
    }

    public function test_empty_history_shows_an_honest_empty_state(): void
    {
        $main = $this->main($this->actingAs($this->student->user)->get('/student/chatbot')->assertOk()->getContent());

        $this->assertStringContainsString('Belum ada percakapan.', $main);
        $this->assertStringContainsString('Mulai percakapan pertama Anda dengan SAGO', $main);
    }

    public function test_conversation_page_has_one_h1_send_icon_and_the_same_endpoints(): void
    {
        $id = $this->conversation($this->student->user_id, $this->student->id, 'Persiapan KKN di pesisir', ['message_count' => 2]);
        $this->message($id, 'user', 'Apa yang perlu disiapkan?');
        $this->message($id, 'assistant', 'Mulailah dengan survei lokasi.');
        $other = $this->conversation($this->student->user_id, $this->student->id, 'Percakapan lain');

        $html = $this->actingAs($this->student->user)->get('/student/chatbot/' . $id)->assertOk()->getContent();
        $main = $this->main($html);

        $this->assertSame(1, substr_count($main, '<h1'));
        $this->assertStringContainsString('<h2 id="chat-title">Persiapan KKN di pesisir</h2>', $main);
        // tombol kirim: ikon send dari sprite + teks
        $this->assertMatchesRegularExpression('#<button type="submit" class="sg-btn sg-btn--primary" id="sendButton"\s*>\s*<svg[^>]*><use href="[^"]*sprite\.svg\#send"></use></svg>\s*Kirim#', $main);
        // penulis pesan tertulis, isi pesan dari data, urutan kronologis
        $this->assertStringContainsString('data-role="user"', $main);
        $this->assertStringContainsString('data-role="assistant"', $main);
        $this->assertLessThan(strpos($main, 'Mulailah dengan survei lokasi.'), strpos($main, 'Apa yang perlu disiapkan?'));
        $this->assertStringContainsString('>SAGO</p>', $main);
        $this->assertStringContainsString('Mulailah dengan survei lokasi.', $main);
        // riwayat: percakapan aktif ditandai, yang lain tautan biasa
        $this->assertMatchesRegularExpression('#href="' . preg_quote(route('student.chatbot.show', $id), '#') . '" class="sg-chat__conversation"\s+aria-current="page"#', $main);
        $this->assertStringContainsString(route('student.chatbot.show', $other), $main);
        // kontrak tetap: name/id input, endpoint kirim, payload, arsip, hapus
        $this->assertStringContainsString('id="messageInput" name="message"', $main);
        $this->assertStringContainsString('maxlength="5000"', $main);
        $this->assertStringContainsString(str_replace('/', '\/', route('student.chatbot.send')), $html);
        $this->assertStringContainsString('conversation_id: ' . $id, $html);
        $this->assertStringContainsString("/\${id}/archive", $html);
        $this->assertStringContainsString("method: 'DELETE'", $html);
        $this->assertStringContainsString('window.sgConfirm', $html);
    }

    public function test_conversation_ownership_and_unknown_id_redirect_are_unchanged(): void
    {
        $someone = $this->makeStudent('Budi', 'Santoso');
        $foreign = $this->conversation($someone->user_id, $someone->id, 'Milik orang lain');

        $this->actingAs($this->student->user)->get('/student/chatbot/' . $foreign)
            ->assertRedirect(route('student.chatbot.index'))->assertSessionHas('error', 'Percakapan tidak ditemukan.');
        $this->actingAs($this->student->user)->get('/student/chatbot/999999')
            ->assertRedirect(route('student.chatbot.index'));
        $this->actingAs($this->student->user)->deleteJson('/student/chatbot/' . $foreign)->assertNotFound();
        $this->assertSame(1, DB::table('chat_conversations')->where('id', $foreign)->count());
    }

    public function test_archive_and_delete_endpoints_still_work(): void
    {
        $a = $this->conversation($this->student->user_id, $this->student->id, 'Arsipkan saya');
        $b = $this->conversation($this->student->user_id, $this->student->id, 'Hapus saya');
        $this->actingAs($this->student->user);

        $this->postJson('/student/chatbot/' . $a . '/archive')->assertOk()->assertJson(['success' => true]);
        $this->assertSame('archived', DB::table('chat_conversations')->where('id', $a)->value('status'));
        $this->deleteJson('/student/chatbot/' . $b)->assertOk()->assertJson(['success' => true]);

        // arsip tidak tampil di riwayat
        $this->assertStringNotContainsString('Arsipkan saya', $this->get('/student/chatbot')->getContent());
    }

    // ── Notifikasi ──────────────────────────────────────────────────────

    public function test_one_template_for_all_three_roles(): void
    {
        $institution = $this->makeInstitution();
        $company = $this->makeCompany();

        foreach ([$this->student->user, $institution->user, $company->user] as $user) {
            $response = $this->actingAs($user)->get('/notifications')->assertOk()->assertViewIs('notifications.index');
            $main = $this->main($response->getContent());
            $this->assertSame(1, substr_count($main, '<h1'), $user->user_type);
            $this->assertStringContainsString('<h1>Notifikasi</h1>', $main);
            $this->assertStringContainsString('sg-inbox', $main);
        }

        $this->get('/logout');
        auth()->logout();
        $this->get('/notifications')->assertRedirect(route('login'));
    }

    public function test_icons_follow_the_type_and_titles_have_no_emoji(): void
    {
        $uid = $this->student->user_id;
        $this->notify($uid, 'application_accepted', 'Aplikasi Diterima! 🎉');
        $this->notify($uid, 'review_received', 'Review Diterima ⭐');
        $this->notify($uid, 'deadline_reminder', 'Pengingat Deadline ⏰', ['is_read' => true, 'read_at' => now()]);
        $this->notify($uid, 'problem_published', 'Masalah Baru Dipublikasikan 📢');

        $main = $this->main($this->actingAs($this->student->user)->get('/notifications')->assertOk()->getContent());

        foreach (['🎉', '⭐', '⏰', '📢', '✅', '📝', '🚀'] as $emoji) {
            $this->assertStringNotContainsString($emoji, $main);
        }
        $this->assertStringContainsString('<p class="sg-inbox__title">Aplikasi Diterima!</p>', $main);
        $this->assertStringContainsString('<p class="sg-inbox__title">Masalah Baru Dipublikasikan</p>', $main);
        foreach (['circle-check', 'award', 'calendar', 'lightbulb'] as $icon) {
            $this->assertStringContainsString('sprite.svg#' . $icon . '"', $main);
        }
        // status baca tertulis, bukan hanya warna
        $this->assertSame(3, substr_count($main, 'Belum dibaca</span>'));
        $this->assertStringContainsString('<span>Sudah dibaca</span>', $main);
        $this->assertStringContainsString('<h2 id="inbox-day-' . now()->toDateString() . '" class="sg-inbox__day">Hari ini</h2>', $main);
    }

    public function test_filters_have_visible_labels_and_only_existing_types(): void
    {
        $uid = $this->student->user_id;
        $this->notify($uid, 'application_rejected', 'Aplikasi Ditolak');
        $this->notify($uid, 'friend_request', 'Permintaan Pertemanan Baru', ['is_read' => true]);

        $main = $this->main($this->actingAs($this->student->user)->get('/notifications')->getContent());

        $this->assertMatchesRegularExpression('#<label class="sg-label"\s+for="filter-status"[^>]*>Status</label>#', $main);
        $this->assertMatchesRegularExpression('#<label class="sg-label"\s+for="filter-type"[^>]*>Tipe notifikasi</label>#', $main);
        // filter tanpa kolom cari (controller tidak membaca parameter search)
        $this->assertStringNotContainsString('name="search"', $main);
        $this->assertStringContainsString('name="status" id="filter-status"', $main);
        $this->assertStringContainsString('name="type" id="filter-type"', $main);
        $this->assertStringContainsString('<option value="application_rejected" >Aplikasi ditolak</option>', $main);
        $this->assertStringContainsString('<option value="friend_request" >Permintaan pertemanan</option>', $main);
        $this->assertStringNotContainsString('value="review_received"', $main);
        $this->assertStringContainsString('action="' . route('notifications.index') . '"', $main);
    }

    public function test_status_and_type_filters_keep_their_query_contract(): void
    {
        $uid = $this->student->user_id;
        $this->notify($uid, 'application_rejected', 'Tidak dibaca A');
        $this->notify($uid, 'friend_request', 'Sudah dibaca B', ['is_read' => true]);
        $this->actingAs($this->student->user);

        $unread = $this->main($this->get('/notifications?status=unread')->getContent());
        $this->assertStringContainsString('Tidak dibaca A', $unread);
        $this->assertStringNotContainsString('Sudah dibaca B', $unread);
        $this->assertStringContainsString('<b>1</b> dari 2 notifikasi cocok dengan filter', $unread);

        $type = $this->main($this->get('/notifications?type=friend_request')->getContent());
        $this->assertStringContainsString('Sudah dibaca B', $type);
        $this->assertStringNotContainsString('Tidak dibaca A', $type);

        $none = $this->main($this->get('/notifications?status=unread&type=friend_request')->getContent());
        $this->assertStringContainsString('Tidak ada notifikasi yang cocok', $none);
        $this->assertStringContainsString('no-search-results.svg', $none);
    }

    public function test_empty_inbox_per_role(): void
    {
        $company = $this->makeCompany();
        $main = $this->main($this->actingAs($company->user)->get('/notifications')->assertOk()->getContent());

        $this->assertStringContainsString('no-notifications.svg', $main);
        $this->assertStringContainsString('Belum ada notifikasi', $main);
        $this->assertStringContainsString(route('company.dashboard'), $main);
        // tanpa filter dan tanpa aksi massal bila kosong
        $this->assertStringNotContainsString('filter-status', $main);
        $this->assertStringNotContainsString(route('notifications.read-all'), $main);
    }

    public function test_opening_the_page_does_not_mark_anything_read(): void
    {
        $id = $this->notify($this->student->user_id, 'project_started', 'Proyek Dimulai 🚀', ['action_url' => '/student/projects']);

        $this->actingAs($this->student->user)->get('/notifications')->assertOk();
        $this->get('/notifications?status=unread')->assertOk();

        $this->assertFalse((bool) DB::table('notifications')->where('id', $id)->value('is_read'));
    }

    public function test_actions_post_to_the_same_endpoints_with_csrf(): void
    {
        $uid = $this->student->user_id;
        $withUrl = $this->notify($uid, 'project_started', 'Proyek Dimulai', ['action_url' => '/student/projects']);
        $plain = $this->notify($uid, 'friend_request', 'Permintaan Pertemanan');
        $this->notify($uid, 'report_approved', 'Laporan Disetujui', ['is_read' => true]);

        $main = $this->main($this->actingAs($this->student->user)->get('/notifications')->getContent());

        $this->assertStringContainsString('action="' . route('notifications.read-all') . '" method="POST"', $main);
        $this->assertMatchesRegularExpression('#action="' . preg_quote(route('notifications.destroy-read'), '#') . '" method="POST"\s+data-sg-confirm=#', $main);
        $this->assertStringContainsString('name="_method" value="DELETE"', $main);
        $this->assertStringContainsString('action="' . route('notifications.read', $withUrl) . '" method="POST"', $main);
        $this->assertStringContainsString('aria-label="Buka: Proyek Dimulai"', $main);
        $this->assertStringContainsString('action="' . route('notifications.read', $plain) . '" method="POST"', $main);
        $this->assertStringContainsString('aria-label="Tandai dibaca: Permintaan Pertemanan"', $main);
        $this->assertStringContainsString('action="' . route('notifications.destroy', $plain) . '" method="POST"', $main);
        $this->assertStringContainsString('aria-label="Hapus notifikasi: Permintaan Pertemanan"', $main);

        // perilaku endpoint tidak berubah
        $this->post(route('notifications.read', $withUrl))->assertRedirect('/student/projects');
        $this->post(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, DB::table('notifications')->where('user_id', $uid)->where('is_read', false)->count());
        $this->delete(route('notifications.destroy-read'))->assertRedirect();
        $this->assertSame(0, DB::table('notifications')->where('user_id', $uid)->count());
    }

    public function test_notifications_of_other_users_stay_unreachable(): void
    {
        $institution = $this->makeInstitution();
        $foreign = $this->notify($institution->user_id, 'application_submitted', 'Aplikasi Baru Diterima');

        $this->actingAs($this->student->user);
        $this->assertStringNotContainsString('Aplikasi Baru Diterima', $this->get('/notifications')->getContent());
        $this->post(route('notifications.read', $foreign))->assertNotFound();
        $this->delete(route('notifications.destroy', $foreign))->assertNotFound();
        $this->assertSame(1, DB::table('notifications')->where('id', $foreign)->count());
    }

    public function test_type_catalogue_and_emoji_cleaning(): void
    {
        $this->assertSame('Aplikasi Diterima!', NotificationTypes::cleanTitle('Aplikasi Diterima! 🎉'));
        $this->assertSame('Laporan Disetujui', NotificationTypes::cleanTitle('Laporan Disetujui ✅'));
        $this->assertSame('Info penting', NotificationTypes::cleanTitle('ℹ️ Info penting'));
        $this->assertSame('Skor #1 © 2026', NotificationTypes::cleanTitle('Skor #1 © 2026'));
        $this->assertSame('bell', NotificationTypes::icon('tipe_tidak_dikenal'));
        $this->assertSame('Lainnya', NotificationTypes::label(null));
        $this->assertSame(['application_submitted' => 'Aplikasi masuk', 'zzz_custom' => 'Lainnya'],
            NotificationTypes::options(['zzz_custom', 'application_submitted', 'application_submitted']));
    }

    public function test_repository_entry_point_uses_the_assistant_name(): void
    {
        $html = view('student.repository.index', [
            'documents' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
            'provinces' => collect(), 'years' => [], 'stats' => ['total_documents' => 0], 'totalDocuments' => 0,
        ])->render();

        $this->assertStringContainsString('Tanya SAGO', $html);
    }

    public function test_system_prompt_introduces_the_assistant_as_sago(): void
    {
        $prompt = app(\App\Services\PromptBuilderService::class)->buildSystemPrompt();

        $this->assertStringContainsString('Kamu adalah SAGO (Student Assistant for Guidance & Opportunities)', $prompt);
        $this->assertStringContainsString('Namamu SAGO', $prompt);
    }

    public function test_ai_failure_does_not_return_exception_details_without_debug(): void
    {
        config(['app.debug' => false, 'services.claude.api_key' => 'kunci-uji']);
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response('SQLSTATE host=db.rahasia.internal', 500)]);
        $this->mock(\App\Services\DocumentRetrieverService::class, function ($mock) {
            $mock->shouldReceive('searchDocuments')->andReturn(collect());
            $mock->shouldReceive('getDocumentExcerpts')->andReturn([]);
        });
        $id = $this->conversation($this->student->user_id, $this->student->id, 'Percakapan Baru');

        $response = $this->actingAs($this->student->user)
            ->postJson(route('student.chatbot.send'), ['conversation_id' => $id, 'message' => 'Apa itu SAGO?']);

        $response->assertJson(['success' => false, 'error' => 'Internal server error']);
        $this->assertStringNotContainsString('db.rahasia.internal', $response->getContent());
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
    }
}
