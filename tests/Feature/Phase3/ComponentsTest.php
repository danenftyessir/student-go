<?php

namespace Tests\Feature\Phase3;

use App\Support\StatusVocabulary;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * phase 3: pemetaan status tunggal (D-02) dan komponen x-ui.* (nama/id input tetap, old(), galat, aksesibilitas)
 */
class ComponentsTest extends TestCase
{
    // nilai tersimpan dari migrasi (DISCOVERY_REPORT S1-20)
    private const STORED = [
        'application' => ['pending', 'reviewed', 'accepted', 'rejected'],
        // phase 21: + nilai yang ditulis pipeline perusahaan (JobApplicationController in:new,…,hired; Student\JobController::quickApply 'new')
        'job_application' => ['pending', 'reviewed', 'shortlisted', 'rejected', 'accepted', 'new', 'reviewing', 'interview', 'offer', 'hired'],
        'problem' => ['draft', 'open', 'in_progress', 'completed', 'closed'],
        'project' => ['active', 'on_hold', 'completed', 'cancelled'],
        'milestone' => ['pending', 'in_progress', 'completed', 'delayed'],
        'report' => ['pending', 'reviewed', 'approved', 'revision_needed'],
        'document' => ['pending', 'approved', 'rejected'],
        // phase 20: + nilai instansi active/suspended (Institution::STATUS_*) dan status per dokumen verifikasi
        'verification' => ['pending_verification', 'verified', 'rejected', 'needs_review', 'active', 'suspended'],
        'verification_document' => ['approved', 'needs_review', 'rejected'],
        'job_posting' => ['draft', 'posted', 'closed', 'archived'],
        'friend' => ['pending', 'accepted', 'rejected', 'blocked'],
    ];

    public function test_every_stored_status_has_exactly_one_known_mapping(): void
    {
        $this->assertSame(array_keys(self::STORED), StatusVocabulary::domains());
        $icons = array_keys(json_decode(file_get_contents(public_path('assets/icons/ui/icons.json')), true));
        foreach (self::STORED as $domain => $values) {
            $this->assertEqualsCanonicalizing($values, array_keys(StatusVocabulary::values($domain)), $domain);
            foreach ($values as $value) {
                $s = StatusVocabulary::resolve($domain, $value);
                $this->assertTrue($s['known']);
                $this->assertContains($s['icon'], $icons, "$domain.$value icon");
                $this->assertContains($s['tone'], ['neutral', 'info', 'success', 'warning', 'danger', 'muted']);
            }
        }
    }

    public function test_application_words_follow_the_approved_deck(): void
    {
        $this->assertSame('Menunggu', StatusVocabulary::label('application', 'pending'));
        $this->assertSame('Ditinjau', StatusVocabulary::label('application', 'reviewed'));
        $this->assertSame('Diterima', StatusVocabulary::label('application', 'accepted'));
        $this->assertSame('Ditolak', StatusVocabulary::label('application', 'rejected'));
        $this->assertSame('Dibatalkan', StatusVocabulary::label('project', 'cancelled'));
        $this->assertSame('Terpilih', StatusVocabulary::label('job_application', 'shortlisted'));
        $this->assertSame('Menunggu', StatusVocabulary::label('application', ' PENDING '));
    }

    public function test_unknown_status_is_shown_as_is_not_invented(): void
    {
        $s = StatusVocabulary::resolve('application', 'withdrawn_by_admin');
        $this->assertFalse($s['known']);
        $this->assertSame('Withdrawn by admin', $s['label']);
        $this->assertSame('neutral', $s['tone']);
        $this->assertSame('—', StatusVocabulary::label('application', null));
    }

    public function test_status_badge_renders_word_icon_and_tone(): void
    {
        $html = Blade::render('<x-ui.status domain="application" value="rejected" />');
        $this->assertStringContainsString('class="sg-status"', $html);
        $this->assertStringContainsString('data-tone="danger"', $html);
        $this->assertStringContainsString('#circle-x', $html);
        $this->assertStringContainsString('Ditolak', $html);
    }

    private function withErrors(array $messages): void
    {
        view()->share('errors', (new ViewErrorBag)->put('default', new MessageBag($messages)));
    }

    public function test_input_keeps_name_and_id_and_binds_server_error(): void
    {
        $this->withErrors(['email' => ['Email wajib diisi.']]);
        $html = Blade::render(
            '<x-ui.field label="Email" for="email" required><x-ui.input name="email" type="email" value="lama@contoh.id" required /></x-ui.field>'
        );
        $this->assertStringContainsString('name="email" id="email"', $html);
        $this->assertStringContainsString('value="lama@contoh.id"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('aria-describedby="email-error"', $html);
        $this->assertStringContainsString('id="email-error"', $html);
        $this->assertStringContainsString('Email wajib diisi.', $html);
        $this->assertMatchesRegularExpression('#<label class="sg-label"\s+for="email"\s+data-required\s*>#', $html);
    }

    public function test_password_input_is_never_repopulated(): void
    {
        $html = Blade::render('<x-ui.input name="password" type="password" value="rahasia" />');
        $this->assertStringNotContainsString('rahasia', $html);
    }

    public function test_select_marks_the_selected_option(): void
    {
        $html = Blade::render('<x-ui.select name="status" :options="[\'pending\' => \'Menunggu\', \'accepted\' => \'Diterima\']" selected="accepted" placeholder="Semua status" />');
        $this->assertStringContainsString('<option value="">Semua status</option>', $html);
        $this->assertMatchesRegularExpression('#<option value="accepted"\s+selected>#', $html);
    }

    public function test_upload_keeps_the_real_file_input(): void
    {
        $html = Blade::render('<x-ui.upload name="proposal" label="Proposal" accept=".pdf" hint="PDF, maks. 5 MB" required />');
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('name="proposal" id="proposal"', $html);
        $this->assertStringContainsString('accept=".pdf"', $html);
        $this->assertStringContainsString('aria-describedby="proposal-help"', $html);
        $this->assertStringContainsString('<label class="sg-dropzone" for="proposal"', $html);
    }

    public function test_pagination_uses_paginator_urls_and_marks_current_page(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 20), 77, 20, 2, ['path' => 'http://localhost/student/repository', 'query' => ['search' => 'air']]);
        $html = Blade::render('<x-ui.pagination :paginator="$p" />', ['p' => $paginator]);
        $this->assertStringContainsString('Menampilkan 21–40 dari 77', $html);
        $this->assertStringContainsString('aria-current="page"><span class="sr-only">Halaman </span>2', $html);
        $this->assertStringContainsString('href="http://localhost/student/repository?search=air&amp;page=3"', $html);
        $this->assertStringContainsString('rel="prev"', $html);
    }

    public function test_avatar_uses_initials_instead_of_generated_placeholder_image(): void
    {
        $html = Blade::render('<x-ui.avatar src="https://ui-avatars.com/api/?name=X" name="Farah Wibowo" />');
        $this->assertStringNotContainsString('ui-avatars.com', $html);
        $this->assertStringContainsString('aria-label="Farah Wibowo"', $html);
        $this->assertStringContainsString('FW', $html);

        $photo = Blade::render('<x-ui.avatar src="https://example.test/foto.jpg" name="Farah Wibowo" />');
        $this->assertStringContainsString('<img src="https://example.test/foto.jpg" alt="Farah Wibowo"', $photo);
    }

    public function test_menu_item_post_renders_a_csrf_form(): void
    {
        $html = Blade::render('<x-ui.menu-item href="/logout" method="post" icon="log-out" danger>Keluar</x-ui.menu-item>');
        $this->assertStringContainsString('<form method="POST" action="/logout">', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('type="submit"', $html);
    }

    public function test_page_header_renders_the_single_h1(): void
    {
        $html = Blade::render('<x-ui.page-header title="Aplikasi Saya" description="Pantau status lamaran." :breadcrumbs="[\'Dashboard\' => \'/student/dashboard\', \'Aplikasi Saya\' => null]" />');
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('aria-current="page">Aplikasi Saya</span>', $html);
    }

    public function test_layout_css_sources_contain_no_gradient_and_shadow_only_on_overlays(): void
    {
        // komentar dibuang: teks aturan ("no gradients") bukan deklarasi
        $css = preg_replace('#/\*.*?\*/#s', '', file_get_contents(resource_path('css/student-go/components.css')));
        $this->assertStringNotContainsString('gradient', $css);
        preg_match_all('#([^{}]+)\{[^}]*box-shadow:[^}]*\}#', $css, $m);
        foreach ($m[1] as $selector) {
            $this->assertMatchesRegularExpression('#sg-overlay|sg-dialog|sg-filter-bar__panel#', $selector);
        }
    }
}
