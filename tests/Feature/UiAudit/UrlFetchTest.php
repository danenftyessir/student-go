<?php

namespace Tests\Feature\UiAudit;

use Tests\TestCase;

/**
 * audit ui/ux F-39: fetch di view memakai route() (bukan path yang ditulis manual), agar ikut berubah bila prefix route berubah.
 */
class UrlFetchTest extends TestCase
{
    private const VIEWS = [
        'company/talents/compare.blade.php' => ['company.talents.toggle-save'],
        'institution/projects/manage.blade.php' => ['institution.projects.update-status', 'institution.projects.get-milestone', 'institution.projects.update-milestone', 'institution.projects.delete-milestone'],
        'student/profile/index.blade.php' => ['student.profile.project.toggle-visibility'],
        'student/projects/index.blade.php' => ['student.projects.toggle-portfolio'],
        'student/projects/show.blade.php' => ['student.projects.milestones.update'],
        'student/wishlist/index.blade.php' => ['student.wishlist.remove'],
    ];

    public function test_fetch_calls_use_named_routes(): void
    {
        foreach (self::VIEWS as $view => $routes) {
            $source = file_get_contents(resource_path('views/'.$view));
            $this->assertDoesNotMatchRegularExpression('#fetch\(\s*[`\'"]/#', $source, $view);
            foreach ($routes as $name) {
                $this->assertStringContainsString("route('$name'", $source, "$view → $name");
            }
        }
    }
}
