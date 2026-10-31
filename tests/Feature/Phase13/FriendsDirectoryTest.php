<?php

namespace Tests\Feature\Phase13;

use App\Models\Friend;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 13: jaringan dan pencarian mahasiswa sebagai direktori orang (S12, S13; J6, J7, J8, J18).
 * endpoint, metode dan parameter query tetap; nama/inisial dari Student::display_name (D-03); tanpa kontak di daftar (D-04).
 */
class FriendsDirectoryTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    private $viewer;
    private int $otherUniversityId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
        $this->otherUniversityId = DB::table('universities')->insertGetId([
            'name' => 'Universitas Padjadjaran', 'code' => 'UNPAD',
            'province_id' => $this->provinceId, 'regency_id' => $this->regencyId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->viewer = $this->makeStudent('Zaki', 'Pratama');
    }

    public function test_search_page_has_page_header_and_no_hero_or_english_copy(): void
    {
        $this->makeStudent('Farah', 'Wibowo');

        $html = $this->actingAs($this->viewer->user)->get('/student/friends/search')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('Temukan Koneksi', $html);
        $this->assertStringNotContainsString('search-network.jpg', $html);
        $this->assertStringNotContainsString('bg-gradient', $html);
        $this->assertStringNotContainsString('Add Connection', $html);
        $this->assertStringNotContainsString('ui-avatars.com', $html);
        $this->assertStringContainsString('Hubungkan', $html);
    }

    public function test_result_count_is_the_filtered_total(): void
    {
        $this->makeStudent('Ayu', 'Lestari');
        $this->makeStudent('Bima', 'Saputra', null, ['university_id' => $this->otherUniversityId, 'major' => 'Kesehatan Masyarakat']);
        $this->makeStudent('Citra', 'Dewi', null, ['university_id' => $this->otherUniversityId, 'major' => 'Teknik Sipil']);

        $all = $this->actingAs($this->viewer->user)->get('/student/friends/search');
        $all->assertSee('data-result-count="3"', false);
        $this->assertMatchesRegularExpression('#<b[^>]*>3</b>\s*mahasiswa ditemukan#', $all->getContent());

        $byUniversity = $this->actingAs($this->viewer->user)->get('/student/friends/search?university_id=' . $this->otherUniversityId);
        $byUniversity->assertSee('data-result-count="2"', false)->assertSee('sesuai filter');
        $this->assertSame(2, $byUniversity->viewData('results')->total());

        $byMajor = $this->actingAs($this->viewer->user)->get('/student/friends/search?major=Teknik');
        $this->assertSame(['Ayu Lestari', 'Citra Dewi'], collect($byMajor->viewData('results')->items())->pluck('display_name')->all());

        $combined = $this->actingAs($this->viewer->user)->get('/student/friends/search?university_id=' . $this->otherUniversityId . '&major=Sipil');
        $combined->assertSee('data-result-count="1"', false)->assertSee('Citra Dewi')->assertDontSee('Bima Saputra');

        $byName = $this->actingAs($this->viewer->user)->get('/student/friends/search?search=Bima');
        $this->assertSame(1, $byName->viewData('results')->total());

        $none = $this->actingAs($this->viewer->user)->get('/student/friends/search?search=Tidakada');
        $none->assertSee('data-result-count="0"', false)->assertSee('Tidak ada mahasiswa yang cocok');
    }

    public function test_quick_filters_keep_their_links_and_work(): void
    {
        $this->makeStudent('Ayu', 'Lestari');
        $this->makeStudent('Bima', 'Saputra', null, ['university_id' => $this->otherUniversityId, 'major' => 'Kesehatan Masyarakat']);

        $html = $this->actingAs($this->viewer->user)->get('/student/friends/search')->getContent();
        $universityHref = route('student.friends.search', ['university_id' => $this->viewer->university_id]);
        $majorHref = route('student.friends.search', ['major' => $this->viewer->major]);
        $this->assertStringContainsString('href="' . e($universityHref) . '"', $html);
        $this->assertStringContainsString('href="' . e($majorHref) . '"', $html);
        $this->assertStringContainsString('Dari Universitas Saya', $html);
        $this->assertStringContainsString('Jurusan yang Sama', $html);

        $sameUniversity = $this->actingAs($this->viewer->user)->get($universityHref);
        $this->assertSame(['Ayu Lestari'], collect($sameUniversity->viewData('results')->items())->pluck('display_name')->all());
        $sameUniversity->assertSee('aria-current="true"', false);

        $sameMajor = $this->actingAs($this->viewer->user)->get($majorHref);
        $this->assertSame(['Ayu Lestari'], collect($sameMajor->viewData('results')->items())->pluck('display_name')->all());
    }

    public function test_filter_form_keeps_parameter_names_and_get_method(): void
    {
        $html = $this->actingAs($this->viewer->user)->get('/student/friends/search')->getContent();

        $this->assertMatchesRegularExpression('#<form method="GET" action="' . preg_quote(route('student.friends.search'), '#') . '"#', $html);
        foreach (['search', 'university_id', 'major', 'skills'] as $name) {
            $this->assertStringContainsString('name="' . $name . '"', $html);
        }
    }

    public function test_rows_show_real_connection_state_and_one_action(): void
    {
        $none = $this->makeStudent('Ayu', 'Lestari');
        $sent = $this->makeStudent('Bima', 'Saputra');
        $received = $this->makeStudent('Citra', 'Dewi');
        $friend = $this->makeStudent('Dian', 'Kusuma');
        $this->makeFriendship($this->viewer, $sent, 'pending');
        $this->makeFriendship($received, $this->viewer, 'pending');
        $this->makeFriendship($friend, $this->viewer, 'accepted');

        $html = $this->actingAs($this->viewer->user)->get('/student/friends/search')->assertOk()->getContent();

        $this->assertSame('none', $this->rowState($html, $none->id));
        $this->assertSame('request_sent', $this->rowState($html, $sent->id));
        $this->assertSame('request_received', $this->rowState($html, $received->id));
        $this->assertSame('friends', $this->rowState($html, $friend->id));

        $this->assertStringContainsString('action="' . route('student.friends.send-request', $none->id) . '"', $html);
        foreach ([$sent, $received, $friend] as $other) {
            $this->assertStringNotContainsString('action="' . route('student.friends.send-request', $other->id) . '"', $html);
        }
        $this->assertStringContainsString('Menunggu', $this->row($html, $sent->id));
        $this->assertStringContainsString('Tanggapi', $this->row($html, $received->id));
        $this->assertStringContainsString('Terhubung', $this->row($html, $friend->id));
    }

    public function test_connect_action_posts_to_the_same_endpoint_and_row_turns_pending(): void
    {
        $target = $this->makeStudent('Ayu', 'Lestari');

        $this->actingAs($this->viewer->user)
            ->from('/student/friends/search')
            ->post(route('student.friends.send-request', $target->id))
            ->assertRedirect('/student/friends/search')
            ->assertSessionHas('success');

        $this->assertSame(1, Friend::where('requester_id', $this->viewer->id)->where('receiver_id', $target->id)->where('status', 'pending')->count());

        $html = $this->actingAs($this->viewer->user)->get('/student/friends/search')->getContent();
        $this->assertSame('request_sent', $this->rowState($html, $target->id));
        $this->assertStringContainsString('Menunggu', $this->row($html, $target->id));
    }

    public function test_row_name_and_initials_equal_the_public_profile(): void
    {
        // users.name sengaja berbeda (data seed lama); daftar dan profil memakai students
        $target = $this->makeStudent('Farah', 'Wibowo', 'Candra Hasanah');

        $search = $this->actingAs($this->viewer->user)->get('/student/friends/search')->getContent();
        $row = $this->row($search, $target->id);
        $this->assertStringContainsString('Farah Wibowo', $row);
        $this->assertStringContainsString('data-initials="FW"', $row);
        $this->assertStringNotContainsString('Candra Hasanah', $search);

        // tautan baris -> redirect lama -> profil publik yang benar
        $href = route('student.friends.profile', $target->id);
        $this->assertStringContainsString('href="' . $href . '"', $row);
        $this->actingAs($this->viewer->user)->get($href)->assertRedirect(route('profile.public', $target->user->username));

        $profile = $this->actingAs($this->viewer->user)->get(route('profile.public', $target->user->username))->assertOk()->getContent();
        $this->assertStringContainsString('Farah Wibowo', $profile);
        $this->assertStringContainsString('data-initials="FW"', $profile);
    }

    public function test_lists_never_contain_contact_details(): void
    {
        $friend = $this->makeStudent('Dian', 'Kusuma');
        $requester = $this->makeStudent('Citra', 'Dewi');
        $stranger = $this->makeStudent('Ayu', 'Lestari');
        $this->makeFriendship($friend, $this->viewer, 'accepted');
        $this->makeFriendship($requester, $this->viewer, 'pending');

        $pages = [
            $this->actingAs($this->viewer->user)->get('/student/friends')->assertOk()->getContent(),
            $this->actingAs($this->viewer->user)->get('/student/friends/search')->assertOk()->getContent(),
        ];

        foreach ($pages as $html) {
            foreach ([$friend, $requester, $stranger] as $other) {
                $this->assertStringNotContainsString($other->user->email, $html);
                $this->assertStringNotContainsString(ltrim($other->phone, '+'), $html);
            }
            $this->assertStringNotContainsString('wa.me', $html);
            $this->assertStringNotContainsString('mailto:', $html);
        }
    }

    public function test_network_page_has_tabs_lists_and_no_hero(): void
    {
        $friend = $this->makeStudent('Dian', 'Kusuma');
        $requester = $this->makeStudent('Citra', 'Dewi');
        $suggestion = $this->makeStudent('Ayu', 'Lestari');
        $this->makeFriendship($friend, $this->viewer, 'accepted');
        $request = Friend::create(['requester_id' => $requester->id, 'receiver_id' => $this->viewer->id, 'status' => 'pending', 'message' => 'Halo, satu lokasi KKN']);

        $html = $this->actingAs($this->viewer->user)->get('/student/friends')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('Jaringan Anda', $html);
        $this->assertStringNotContainsString('network-student.JPG', $html);
        $this->assertStringNotContainsString('css/friends.css', $html);
        $this->assertStringNotContainsString('Add Connection', $html);
        $this->assertStringContainsString('Tambah Koneksi', $html);
        $this->assertStringContainsString('role="tablist"', $html);
        foreach (['Koneksi (1)', 'Permintaan (1)', 'Saran'] as $tab) {
            $this->assertStringContainsString('>' . $tab . '</button>', $html);
        }
        // ada permintaan masuk -> tab permintaan terbuka lebih dulu
        $this->assertMatchesRegularExpression('#id="jaringan-permintaan-tab"[^>]*aria-selected="true"#', $html);

        $this->assertStringContainsString('href="' . route('profile.public', $friend->user->username) . '"', $html);
        $this->assertStringContainsString('action="' . route('student.friends.accept', $request->id) . '"', $html);
        $this->assertStringContainsString('action="' . route('student.friends.reject', $request->id) . '"', $html);
        $this->assertStringContainsString('Halo, satu lokasi KKN', $html);
        $this->assertStringContainsString('action="' . route('student.friends.send-request', $suggestion->id) . '"', $html);
    }

    public function test_network_search_filters_connections_server_side(): void
    {
        $this->makeFriendship($this->makeStudent('Dian', 'Kusuma'), $this->viewer, 'accepted');
        $this->makeFriendship($this->viewer, $this->makeStudent('Eka', 'Putri'), 'accepted');

        $response = $this->actingAs($this->viewer->user)->get('/student/friends?search=eka');
        $response->assertOk()->assertSee('Eka Putri')->assertDontSee('Dian Kusuma');
        $this->assertMatchesRegularExpression('#<b[^>]*>1</b>\s*dari 2 koneksi#', $response->getContent());
        $this->assertMatchesRegularExpression('#id="jaringan-koneksi-tab"[^>]*aria-selected="true"#', $response->getContent());
    }

    public function test_empty_network_keeps_people_icon_and_invitation(): void
    {
        $html = $this->actingAs($this->viewer->user)->get('/student/friends')->assertOk()->getContent();

        $this->assertStringContainsString('Belum ada koneksi', $html);
        $this->assertStringContainsString('#users"', $html);
        // .sg-empty svg membesarkan setiap svg: hanya ikon orang yang boleh ada di blok kosong
        preg_match('#<div class="sg-empty">.*?Cari mahasiswa#s', $html, $empty);
        $this->assertSame(1, substr_count($empty[0] ?? '', '<svg'));
        $this->assertStringContainsString('href="' . route('student.friends.search') . '"', $html);
        $this->assertStringContainsString('Tidak ada permintaan masuk', $html);
    }

    public function test_search_paginates_with_the_shared_pager_and_keeps_query_string(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->makeStudent('Sari', 'Putri');
        }

        $html = $this->actingAs($this->viewer->user)->get('/student/friends/search?major=Teknik')->getContent();
        $this->assertStringContainsString('sg-pagination', $html);
        $this->assertStringContainsString('Menampilkan 1–20 dari 25', $html);
        $this->assertStringContainsString(e(route('student.friends.search', ['major' => 'Teknik', 'page' => 2])), $html);
    }

    private function row(string $html, int $studentId): string
    {
        $this->assertSame(1, preg_match('#<li[^>]*data-student-id="' . $studentId . '"[^>]*>.*?</li>#s', $html, $m), 'baris mahasiswa ' . $studentId . ' tidak ditemukan');

        return $m[0];
    }

    private function rowState(string $html, int $studentId): ?string
    {
        preg_match('#data-student-id="' . $studentId . '" data-friendship="([a-z_]+)"#', $html, $m);

        return $m[1] ?? null;
    }
}
