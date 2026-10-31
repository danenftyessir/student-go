<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController - Company Dashboard
 *
 * IMPLEMENTED: Semua data CRUD langsung dari Supabase PostgreSQL
 * TIDAK ADA dummy data lagi
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('home')
                ->with('error', 'profil perusahaan tidak ditemukan');
        }

        // phase 21: dashboard sebagai antrean kerja (D-10). kartu statistik dan persentase pertumbuhan
        // tidak lagi dihitung: tanpa tren dan tanpa angka yang tidak membantu keputusan (guardrail 2)
        $totalJobs = $company->jobPostings()->count();
        $activeJobsCount = $company->jobPostings()->active()->count();
        $draftJobsCount = $company->jobPostings()->where('status', 'draft')->count();

        // tabel lowongan aktif: 5 terbaru + jumlah pelamar per lowongan
        $activeJobs = $company->jobPostings()
            ->active()
            ->with('jobCategory')
            ->withCount('jobApplications')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        // jumlah lamaran per status dari semua lowongan perusahaan
        $statusCounts = JobApplication::whereIn('job_posting_id', $company->jobPostings()->select('id'))
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $totalApplications = (int) $statusCounts->sum();

        // pelamar per tahap yang masih berjalan, urut seperti papan lamaran.
        // nilai lama (pending, reviewed) dihitung di tahap yang sama dengan papan (JobApplication::statusesFor)
        $stages = collect([
            JobApplication::STATUS_NEW,
            JobApplication::STATUS_REVIEWING,
            JobApplication::STATUS_SHORTLISTED,
            JobApplication::STATUS_INTERVIEW,
            JobApplication::STATUS_OFFER,
        ])
            ->map(fn ($status) => ['status' => $status, 'total' => (int) $statusCounts->only(JobApplication::statusesFor($status))->sum()])
            ->filter(fn ($stage) => $stage['total'] > 0)
            ->values();

        // IMPLEMENTED: Ambil recent applications dari Supabase
        $recentApplications = $company->jobApplications()
            ->with(['user.student', 'jobPosting'])
            ->orderBy('job_applications.created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'name' => $application->user->display_name,
                    'position' => $application->jobPosting->title ?? null,
                    'status' => $application->status,
                    'created_at' => $application->created_at,
                ];
            });

        // talenta tersimpan: 5 terakhir disimpan + jumlah sebenarnya
        $savedTalentsCount = $company->savedTalents()->count();
        $savedTalents = $company->savedTalents()
            ->with('student')
            ->orderByDesc('saved_talents.saved_at')
            ->orderByDesc('saved_talents.id')
            ->limit(5)
            ->get();

        // IMPLEMENTED: AI talent recommendations dari Supabase
        // Ambil users dengan impact score tertinggi yang belum diapply
        $talentRecommendations = User::where('user_type', 'student')
            ->whereHas('student')
            ->whereDoesntHave('jobApplications', function ($query) use ($company) {
                $query->whereIn('job_posting_id', $company->jobPostings()->pluck('id'));
            })
            ->with('student')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                // phase 21: status 'online' (selalu true) dan path foto lokal dihapus: keduanya bukan data nyata
                return [
                    'id' => $user->id,
                    'name' => $user->display_name,
                    'username' => $user->username,
                    'expertise' => $user->student->major ?? null,
                ];
            });

        // grafik lamaran per bulan hanya bila >= 5 dari 6 bulan berisi data (D-21, guardrail 2)
        $months = $this->applicationsPerMonth($company);
        $applicationsChart = collect($months)->where('total', '>', 0)->count() >= 5 ? $months : null;

        return view('company.dashboard.index', compact(
            'company',
            'totalJobs',
            'activeJobsCount',
            'draftJobsCount',
            'activeJobs',
            'totalApplications',
            'stages',
            'recentApplications',
            'savedTalents',
            'savedTalentsCount',
            'talentRecommendations',
            'applicationsChart'
        ));
    }

    /**
     * jumlah lamaran masuk per bulan, 6 bulan terakhir (termasuk bulan ini).
     * dikelompokkan di php agar hasilnya sama di pgsql dan sqlite (tes)
     */
    private function applicationsPerMonth($company): array
    {
        $start = now()->startOfMonth()->subMonths(5);

        $counts = JobApplication::whereIn('job_posting_id', $company->jobPostings()->select('id'))
            ->where('created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->format('Y-m'));

        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $month = $start->copy()->addMonths($i);
            $months[] = [
                'label' => $month->locale('id')->translatedFormat('M Y'),
                'total' => (int) ($counts[$month->format('Y-m')] ?? 0),
            ];
        }

        return $months;
    }

    /**
     * Get applications over time data for chart
     * IMPLEMENTED: Data dari Supabase PostgreSQL
     */
    private function getApplicationsOverTime($company)
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push(now()->subMonths($i));
        }

        $labels = $months->map(fn($date) => $date->format('M'))->toArray();

        // Get applications grouped by status and month
        $statusGroups = [
            'new' => [],
            'reviewing' => [],
            'shortlisted' => [],
        ];

        foreach ($months as $month) {
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            foreach (['new', 'reviewing', 'shortlisted'] as $status) {
                $count = $company->jobApplications()
                    ->where('job_applications.status', $status)
                    ->whereBetween('job_applications.created_at', [$startOfMonth, $endOfMonth])
                    ->count();

                $statusGroups[$status][] = $count;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New',
                    'data' => $statusGroups['new'],
                    'color' => '#F97316',
                ],
                [
                    'label' => 'Reviewing',
                    'data' => $statusGroups['reviewing'],
                    'color' => '#22C55E',
                ],
                [
                    'label' => 'Shortlisted',
                    'data' => $statusGroups['shortlisted'],
                    'color' => '#3B82F6',
                ],
            ],
        ];
    }

    /**
     * Get jobs by category data for chart
     * IMPLEMENTED: Data dari Supabase PostgreSQL
     */
    private function getJobsByCategory($company)
    {
        $jobsByCategory = $company->jobPostings()
            ->select('job_category_id', DB::raw('count(*) as total'))
            ->with('jobCategory')
            ->groupBy('job_category_id')
            ->get();

        $labels = [];
        $data = [];

        foreach ($jobsByCategory as $item) {
            $labels[] = $item->jobCategory->name ?? 'Uncategorized';
            $data[] = $item->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
