<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Problem;
use App\Models\Application;
use App\Models\Project;
use App\Models\Review;
use App\Services\AnalyticsService;
use App\Services\ReviewService;

/**
 * controller untuk dashboard instansi
 */
class DashboardController extends Controller
{
    protected $analyticsService;
    protected $reviewService;

    public function __construct(AnalyticsService $analyticsService, ReviewService $reviewService)
    {
        $this->analyticsService = $analyticsService;
        $this->reviewService = $reviewService;
    }

    /**
     * tampilkan dashboard instansi
     */
    public function index()
    {
        $institution = auth()->user()->institution;

        // statistik dashboard menggunakan analytics service
        $stats = $this->analyticsService->getInstitutionAnalytics($institution->id);

        // phase 17: tren bulanan, rekomendasi, dan data grafik tidak lagi dihitung di sini
        // (tidak ditampilkan; D-10 antrean kerja, D-21 tanpa grafik). method privatnya tetap ada.

        // recent applications dengan prioritas pending
        $recentApplications = Application::with(['student.user', 'student.university', 'problem'])
                                        ->whereHas('problem', function($q) use ($institution) {
                                            $q->where('institution_id', $institution->id);
                                        })
                                        ->where(function($query) {
                                            $query->where('status', 'pending')
                                                  ->orWhere('status', 'reviewed');
                                        })
                                        ->latest()
                                        ->limit(5)
                                        ->get();

        // urgent items untuk alert
        $urgentItems = [
            // menunggu keputusan = pending + reviewed (key 'under_review' di analytics menghitung status 'reviewed')
            'pending_applications' => $stats['applications']['pending'] + $stats['applications']['under_review'],
            'pending_reviews' => 0, // akan dihitung dari pending reviews
            'overdue_milestones' => 0, // TODO: implementasi milestone tracking
        ];

        // active projects, dengan milestone berikutnya dan laporan yang menunggu tinjauan
        $activeProjects = Project::where('institution_id', $institution->id)
                                ->where('status', 'active')
                                ->with(['student.user', 'milestones' => function($q) {
                                    $q->where('status', '!=', 'completed')->orderBy('target_date');
                                }])
                                ->withCount(['reports as pending_reports_count' => function($q) {
                                    $q->where('status', 'pending');
                                }])
                                ->limit(5)
                                ->get();

        // pending reviews - proyek completed yang belum di-review
        // phase 19 (disetujui user): "sudah diulas" = ada review institution_to_student, aturan yang sama dengan
        // ReviewController@create (projects.reviewed_at kosong pada data seed walau ulasannya ada)
        $reviewedProjectIds = Review::where('type', 'institution_to_student')->select('project_id');
        $pendingReviews = Project::where('institution_id', $institution->id)
                                ->where('status', 'completed')
                                ->whereNotNull('actual_end_date')
                                ->whereNotIn('id', $reviewedProjectIds)
                                ->with(['student.user'])
                                ->limit(5)
                                ->get();

        // update urgent items dengan pending reviews count (jumlah sebenarnya, bukan dibatasi 5)
        $urgentItems['pending_reviews'] = Project::where('institution_id', $institution->id)
                                ->where('status', 'completed')
                                ->whereNotNull('actual_end_date')
                                ->whereNotIn('id', $reviewedProjectIds)
                                ->count();

        // masalah draf yang belum diterbitkan
        $draftProblems = Problem::where('institution_id', $institution->id)
                                ->where('status', 'draft')
                                ->latest('updated_at')
                                ->limit(5)
                                ->get();
        $draftProblemsCount = Problem::where('institution_id', $institution->id)
                                ->where('status', 'draft')
                                ->count();

        // recent problems
        $recentProblems = Problem::where('institution_id', $institution->id)
                                ->withCount('applications')
                                ->latest()
                                ->limit(5)
                                ->get();

        return view('institution.dashboard.index', compact(
            'stats',
            'recentApplications',
            'institution',
            'urgentItems',
            'activeProjects',
            'pendingReviews',
            'draftProblems',
            'draftProblemsCount',
            'recentProblems'
        ));
    }

    /**
     * get chart data untuk dashboard (AJAX)
     */
    public function getChartData(Request $request)
    {
        $institution = auth()->user()->institution;
        $days = $request->input('days', 30);

        $timeSeriesData = $this->analyticsService->getTimeSeriesData($institution->id, $days);

        return response()->json([
            'success' => true,
            'data' => $timeSeriesData
        ]);
    }

    /**
     * get sdg distribution data (AJAX)
     */
    public function getSdgDistribution()
    {
        $institution = auth()->user()->institution;

        $sdgData = $this->analyticsService->getProblemsBySdgCategory($institution->id);

        return response()->json([
            'success' => true,
            'data' => $sdgData
        ]);
    }

    /**
     * export dashboard report
     */
    public function exportReport(Request $request)
    {
        $institution = auth()->user()->institution;
        $format = $request->input('format', 'json');

        $report = $this->analyticsService->exportFullReport($institution->id, $format);

        if ($format === 'json') {
            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        }

        // untuk format lain seperti PDF atau Excel, bisa ditambahkan nanti
        return response()->download($report);
    }
}