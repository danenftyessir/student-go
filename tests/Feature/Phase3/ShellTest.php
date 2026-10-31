<?php

namespace Tests\Feature\Phase3;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Phase1\Concerns\BuildsPhase1Data;
use Tests\TestCase;

/**
 * phase 3: shell bersama (layouts.app + navbar + footer). label navigasi bahasa indonesia per peran (D-02),
 * lang="id", pola judul "… — Student Go", logout tetap POST + CSRF, pustaka cdn lama hilang (D-20).
 */
class ShellTest extends TestCase
{
    use RefreshDatabase;
    use BuildsPhase1Data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRegion();
    }

    private function navLabels(string $html): array
    {
        preg_match('#<nav class="sg-nav" aria-label="Navigasi utama">(.*?)</nav>#s', $html, $m);
        preg_match_all('#class="sg-nav__link"[^>]*>([^<]+)</a>#', $m[1] ?? '', $labels);

        return array_map('trim', $labels[1]);
    }

    private function assertShell(string $html): void
    {
        $this->assertStringContainsString('<html lang="id">', $html);
        $this->assertMatchesRegularExpression('#<title>[^<]+ — Student Go</title>#u', $html);
        $this->assertStringNotContainsString('Laravel', $this->title($html));
        $this->assertStringContainsString('href="#main"', $html);
        $this->assertStringContainsString('id="main"', $html);
        $this->assertStringContainsString('assets/brand/favicon.svg', $html);
        $this->assertStringContainsString('assets/brand/student-go-logo.svg', $html);
        foreach (['font-awesome', 'boxicons', 'fonts.bunny.net', 'Outfit:wght'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, $gone);
        }
        // footer: tautan privasi ke '#' disembunyikan (D-23)
        $this->assertStringNotContainsString('Kebijakan Privasi', $html);
    }

    private function title(string $html): string
    {
        preg_match('#<title>([^<]*)</title>#', $html, $m);

        return $m[1] ?? '';
    }

    public function test_student_shell_has_indonesian_nav_and_post_logout(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');
        $html = $this->actingAs($student->user)->get('/notifications')->assertOk()->getContent();

        $this->assertShell($html);
        $this->assertSame(['Dashboard', 'Telusuri Masalah', 'Aplikasi Saya', 'Proyek Saya', 'Repositori', 'Lowongan'], $this->navLabels($html));
        $this->assertStringContainsString('Mahasiswa', $html);
        $this->assertStringContainsString(route('student.wishlist.index'), $html);
        $this->assertStringContainsString(route('student.friends.index'), $html);
        // keluar: form POST ke route logout dengan token csrf (desktop dan mobile)
        $this->assertSame(2, preg_match_all('#<form method="POST" action="'.preg_quote(route('logout'), '#').'">\s*<input type="hidden" name="_token"#', $html));
        $this->assertStringContainsString('Farah Wibowo', $html);
    }

    public function test_institution_shell(): void
    {
        $institution = $this->makeInstitution();
        $html = $this->actingAs($institution->user)->get('/notifications')->assertOk()->getContent();

        $this->assertShell($html);
        $this->assertSame(['Dashboard', 'Masalah', 'Aplikasi', 'Proyek', 'Ulasan'], $this->navLabels($html));
        $this->assertStringContainsString('Instansi', $html);
    }

    public function test_company_shell_has_no_english_nav_labels(): void
    {
        $company = $this->makeCompany();
        $html = $this->actingAs($company->user)->get('/notifications')->assertOk()->getContent();

        $this->assertShell($html);
        $labels = $this->navLabels($html);
        $this->assertSame(['Dashboard', 'Talenta', 'Lowongan', 'Lamaran', 'Talenta Tersimpan'], $labels);
        foreach (['Talents', 'Jobs', 'Applications', 'Saved Talents', 'Logout'] as $english) {
            $this->assertStringNotContainsString('>'.$english.'<', $html, $english);
        }
        $this->assertStringContainsString('Perusahaan', $html);
    }

    public function test_guest_shell_and_page_title_suffix_is_normalised(): void
    {
        // halaman menulis akhiran merek sendiri; shell menggantinya dengan pola tunggal
        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertShell($html);
        $this->assertSame('Tentang Kami — Student Go', $this->title($html));
        $this->assertSame(['Tentang Kami', 'Kontak'], $this->navLabels($html));
        $this->assertStringContainsString('href="'.route('login').'"', $html);
        $this->assertStringContainsString('href="'.route('register').'"', $html);
    }

    public function test_bell_and_account_menu_expose_accessible_state(): void
    {
        $student = $this->makeStudent('Farah', 'Wibowo');
        $html = $this->actingAs($student->user)->get('/notifications')->getContent();

        $this->assertStringContainsString('aria-controls="sg-notification-panel"', $html);
        $this->assertStringContainsString(':aria-label="label"', $html);
        $this->assertStringContainsString('aria-controls="sg-account-menu"', $html);
        $this->assertStringContainsString('aria-controls="sg-mobile-nav"', $html);
        $this->assertGreaterThanOrEqual(3, substr_count($html, ':aria-expanded="open.toString()"'));
        // endpoint notifikasi tidak berubah
        $this->assertStringContainsString(str_replace('/', '\/', route('notifications.latest')), $html);
    }
}
