<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * JobPostingController - Manage Job Postings
 *
 * IMPLEMENTED: Semua operasi CRUD langsung ke Supabase PostgreSQL
 * TIDAK ADA data yang disimpan di local database
 */
class JobPostingController extends Controller
{
    protected $supabase;

    // nilai status yang tersimpan (migrasi job_postings; label dari StatusVocabulary 'job_posting')
    private const STATUSES = ['posted', 'draft', 'closed', 'archived'];

    // phase 23: label bahasa indonesia untuk nilai job_type yang tersimpan (nilainya tidak berubah)
    public const JOB_TYPE_LABELS = [
        'Full-time' => 'Penuh waktu',
        'Part-time' => 'Paruh waktu',
        'Contract' => 'Kontrak',
        'Internship' => 'Magang',
        'Freelance' => 'Lepas',
    ];

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('home')
                ->with('error', 'profil perusahaan tidak ditemukan');
        }

        // phase 23: parameter yang sudah ditulis form lama (?search=, ?status=, ?category=) kini diterapkan di server.
        // status memakai nilai tersimpan; 'published' (nilai opsi lama) diterima sebagai 'posted'.
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $status = $status === 'published' ? 'posted' : $status;
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $categoryId = (int) $request->query('category') ?: null;

        // jumlah per status untuk tab (lowongan perusahaan ini saja; soft delete tidak dihitung)
        $statusCounts = $company->jobPostings()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();

        // IMPLEMENTED: Ambil data job postings dari Supabase
        $query = $company->jobPostings()
            ->with('jobCategory')
            ->withCount('jobApplications');

        if ($search !== '') {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search)) . '%';
            $query->where(function ($match) use ($like) {
                $match->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$like])
                    ->orWhereHas('jobCategory', fn ($category) => $category->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like]));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('job_category_id', $categoryId);
        }

        $jobPostings = $query
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // tie-breaker (D-05)
            ->paginate(10)
            ->withQueryString();

        // jumlah pelamar seluruh lowongan perusahaan (bukan hanya halaman ini)
        $totalApplicants = $company->jobPostings()->withCount('jobApplications')->get()->sum('job_applications_count');

        // IMPLEMENTED: Ambil data job categories dari Supabase
        $categories = JobCategory::orderBy('name', 'asc')->get();

        $filters = ['search' => $search, 'status' => $status, 'category' => $categoryId];

        return view('company.jobs.index', compact('company', 'jobPostings', 'categories', 'statusCounts', 'totalApplicants', 'filters'));
    }

    public function create()
    {
        $user = Auth::user();
        $company = $user->company;

        // saran departemen (isian bebas; phase 23: contoh bahasa indonesia)
        $departments = [
            'Pemberdayaan Masyarakat', 'Teknologi', 'Pendidikan', 'Kesehatan', 'Lingkungan',
            'Pertanian', 'Pemasaran', 'Keuangan', 'Operasional', 'Riset & Kebijakan'
        ];

        // daftar job types untuk dropdown: nilai tersimpan => label (phase 23)
        $jobTypes = self::JOB_TYPE_LABELS;

        // daftar skills untuk multi-select (KKN-focused)
        $availableSkills = [
            // Pendidikan & Pengajaran
            'Mengajar', 'Literasi', 'Bimbingan Belajar', 'Pendidikan Anak',

            // Kesehatan & Sanitasi
            'Penyuluhan Kesehatan', 'Sanitasi Lingkungan', 'Gizi & Nutrisi', 'Kesehatan Ibu & Anak',

            // Pertanian & Peternakan
            'Pertanian Organik', 'Budidaya Tanaman', 'Peternakan', 'Hidroponik', 'Kompos',

            // Bisnis & Kewirausahaan
            'UMKM', 'Pemasaran Produk Lokal', 'Manajemen Usaha', 'Koperasi', 'E-commerce',

            // Infrastruktur & Konstruksi
            'Renovasi Fasilitas', 'Pembangunan', 'Pengecatan', 'Pertukangan',

            // Lingkungan
            'Pengelolaan Sampah', 'Daur Ulang', 'Reboisasi', 'Konservasi Air', 'Bank Sampah',

            // Sosial & Kemasyarakatan
            'Pemberdayaan Masyarakat', 'Posyandu', 'PKK', 'Karang Taruna', 'Gotong Royong',

            // Teknologi & Digital
            'Komputer Dasar', 'Media Sosial', 'Desain Grafis', 'Fotografi', 'Videografi',

            // Seni & Budaya
            'Kesenian Daerah', 'Kerajinan Tangan', 'Musik', 'Tari', 'Bahasa Daerah',

            // Administrasi & Dokumentasi
            'Administrasi Desa', 'Dokumentasi', 'Laporan', 'Data Entry', 'Survei'
        ];

        // daftar SDG untuk alignment: nama bahasa indonesia dari sdg_label() (phase 23; id tetap 1-17)
        $sdgOptions = collect(range(1, 17))->map(fn ($id) => ['id' => $id, 'name' => 'SDG ' . $id . ' · ' . sdg_label($id)])->all();

        // kategori lowongan (bug fix: wizard sebelumnya tidak punya field kategori)
        $categories = JobCategory::orderBy('name')->pluck('name', 'id')->all();

        return view('company.jobs.create', compact(
            'company',
            'categories',
            'departments',
            'jobTypes',
            'availableSkills',
            'sdgOptions'
        ));
    }

    public function store(Request $request)
    {
        // IMPLEMENTED: Validasi dan simpan job posting ke Supabase
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'job_category_id' => 'nullable|exists:job_categories,id',
            'department' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'job_type' => 'required|string|max:50',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|max:10',
            'salary_range' => 'nullable|string|max:255', // phase 23: isian wizard, dipetakan ke salary_min/max
            'description' => 'required|string',
            'responsibilities' => 'required|string',
            'qualifications' => 'required|string',
            'benefits' => 'nullable|string',
            'skills' => 'required|array|min:1',
            'sdg_alignment' => 'nullable|array',
            'impact_metrics' => 'nullable|string',
            'success_criteria' => 'nullable|string',
            'status' => 'nullable|in:draft,posted',
            'allow_guest_applications' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $company = $user->company;

        // Generate slug dari title (unik, termasuk lowongan yang sudah dihapus; bug fix: judul sama -> HTTP 500)
        $slug = $this->uniqueSlug($validated['title']);

        // phase 23: rentang gaji teks dari wizard (opsional) -> salary_min/salary_max/currency/period
        $salary = $this->parseSalaryRange($validated['salary_range'] ?? null);

        // IMPLEMENTED: Simpan ke Supabase PostgreSQL
        $jobPosting = JobPosting::create([
            'company_id' => $company->id,
            'job_category_id' => $validated['job_category_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'department' => $validated['department'],
            'location' => $validated['location'],
            'job_type' => $validated['job_type'],
            'salary_min' => $validated['salary_min'] ?? $salary['min'],
            'salary_max' => $validated['salary_max'] ?? $salary['max'],
            'salary_currency' => $validated['salary_currency'] ?? $salary['currency'] ?? 'USD',
            'salary_period' => $salary['period'],
            'description' => $validated['description'],
            'responsibilities' => $validated['responsibilities'],
            'qualifications' => $validated['qualifications'],
            'benefits' => $validated['benefits'] ?? null,
            'skills' => array_values($validated['skills']),
            'sdg_alignment' => isset($validated['sdg_alignment']) ? array_values($validated['sdg_alignment']) : null,
            'impact_metrics' => $validated['impact_metrics'] ?? null,
            'success_criteria' => $validated['success_criteria'] ?? null,
            'status' => $validated['status'] ?? 'posted',
            'allow_guest_applications' => $validated['allow_guest_applications'] ?? false,
            'published_at' => now(),
        ]);

        return redirect()->route('company.jobs.show', $jobPosting->id)
            ->with('success', 'Lowongan berhasil dibuat!');
    }

    public function show($id)
    {
        $user = Auth::user();
        $company = $user->company;

        // IMPLEMENTED: Ambil data job posting dari Supabase
        $jobPosting = JobPosting::with(['jobCategory', 'jobApplications.user'])
            ->where('id', $id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        // views tidak ditambah di sini: halaman ini hanya dibuka pemilik lowongan;
        // kunjungan mahasiswa tetap dihitung oleh Student\JobController::show

        // IMPLEMENTED: Ambil recent applicants dari Supabase
        $recentApplicants = $jobPosting->jobApplications()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'name' => $application->user->display_name ?? 'Unknown',
                    'avatar' => $application->user->avatar ?? null,
                    'is_new' => $application->status === 'new',
                    'status' => $application->status,
                    'created_at' => $application->created_at,
                ];
            });

        // IMPLEMENTED: Ambil statistik dari Supabase
        $statistics = [
            'applications_received' => $jobPosting->applications_count,
            'total_views' => $jobPosting->views_count,
            'shortlisted' => $jobPosting->getApplicationsCountByStatus('shortlisted'),
            'offers_extended' => $jobPosting->getApplicationsCountByStatus('offer'),
            'interviewed' => $jobPosting->getApplicationsCountByStatus('interview'),
            'hired' => $jobPosting->getApplicationsCountByStatus('hired'),
        ];

        // phase 23: pelamar per status tersimpan (label dari StatusVocabulary 'job_application')
        $stageCounts = $jobPosting->jobApplications()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
        $jobTypeLabels = self::JOB_TYPE_LABELS;

        return view('company.jobs.show', compact(
            'company',
            'jobPosting',
            'recentApplicants',
            'statistics',
            'stageCounts',
            'jobTypeLabels'
        ));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $company = $user->company;

        // IMPLEMENTED: Ambil data job posting dari Supabase untuk diedit
        $jobPosting = JobPosting::where('id', $id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        // Data untuk form
        $departments = [
            'Engineering', 'Marketing', 'Design', 'HR', 'Sales',
            'Finance', 'Operations', 'Product', 'Customer Support', 'Legal'
        ];

        $jobTypes = [
            'Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'
        ];

        $availableSkills = [
            // Pendidikan & Pengajaran
            'Mengajar', 'Literasi', 'Bimbingan Belajar', 'Pendidikan Anak',

            // Kesehatan & Sanitasi
            'Penyuluhan Kesehatan', 'Sanitasi Lingkungan', 'Gizi & Nutrisi', 'Kesehatan Ibu & Anak',

            // Pertanian & Peternakan
            'Pertanian Organik', 'Budidaya Tanaman', 'Peternakan', 'Hidroponik', 'Kompos',

            // Bisnis & Kewirausahaan
            'UMKM', 'Pemasaran Produk Lokal', 'Manajemen Usaha', 'Koperasi', 'E-commerce',

            // Infrastruktur & Konstruksi
            'Renovasi Fasilitas', 'Pembangunan', 'Pengecatan', 'Pertukangan',

            // Lingkungan
            'Pengelolaan Sampah', 'Daur Ulang', 'Reboisasi', 'Konservasi Air', 'Bank Sampah',

            // Sosial & Kemasyarakatan
            'Pemberdayaan Masyarakat', 'Posyandu', 'PKK', 'Karang Taruna', 'Gotong Royong',

            // Teknologi & Digital
            'Komputer Dasar', 'Media Sosial', 'Desain Grafis', 'Fotografi', 'Videografi',

            // Seni & Budaya
            'Kesenian Daerah', 'Kerajinan Tangan', 'Musik', 'Tari', 'Bahasa Daerah',

            // Administrasi & Dokumentasi
            'Administrasi Desa', 'Dokumentasi', 'Laporan', 'Data Entry', 'Survei'
        ];

        $sdgOptions = [
            ['id' => 1, 'name' => 'SDG 1: No Poverty', 'color' => 'red'],
            ['id' => 2, 'name' => 'SDG 2: Zero Hunger', 'color' => 'yellow'],
            ['id' => 3, 'name' => 'SDG 3: Good Health And Well-being', 'color' => 'green'],
            ['id' => 4, 'name' => 'SDG 4: Quality Education', 'color' => 'red'],
            ['id' => 5, 'name' => 'SDG 5: Gender Equality', 'color' => 'orange'],
            ['id' => 6, 'name' => 'SDG 6: Clean Water And Sanitation', 'color' => 'blue'],
            ['id' => 7, 'name' => 'SDG 7: Affordable And Clean Energy', 'color' => 'yellow'],
            ['id' => 8, 'name' => 'SDG 8: Decent Work And Economic Growth', 'color' => 'red'],
            ['id' => 9, 'name' => 'SDG 9: Industry, Innovation, And Infrastructure', 'color' => 'orange'],
            ['id' => 10, 'name' => 'SDG 10: Reduced Inequalities', 'color' => 'pink'],
            ['id' => 11, 'name' => 'SDG 11: Sustainable Cities And Communities', 'color' => 'amber'],
            ['id' => 12, 'name' => 'SDG 12: Responsible Consumption And Production', 'color' => 'yellow'],
            ['id' => 13, 'name' => 'SDG 13: Climate Action', 'color' => 'green'],
            ['id' => 14, 'name' => 'SDG 14: Life Below Water', 'color' => 'blue'],
            ['id' => 15, 'name' => 'SDG 15: Life On Land', 'color' => 'green'],
            ['id' => 16, 'name' => 'SDG 16: Peace, Justice, And Strong Institutions', 'color' => 'blue'],
            ['id' => 17, 'name' => 'SDG 17: Partnerships For The Goals', 'color' => 'blue'],
        ];

        $categories = JobCategory::orderBy('name')->get();

        return view('company.jobs.edit', compact(
            'company',
            'jobPosting',
            'departments',
            'jobTypes',
            'availableSkills',
            'sdgOptions',
            'categories'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;

        // IMPLEMENTED: Validasi dan update job posting di Supabase
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'job_category_id' => 'nullable|exists:job_categories,id',
            'department' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'job_type' => 'required|string|max:50',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|max:10',
            'description' => 'required|string',
            'responsibilities' => 'required|string',
            'qualifications' => 'required|string',
            'benefits' => 'nullable|string',
            'skills' => 'required|array|min:1',
            'sdg_alignment' => 'nullable|array',
            'impact_metrics' => 'nullable|string',
            'success_criteria' => 'nullable|string',
            'status' => 'nullable|in:draft,posted,closed,archived',
            'allow_guest_applications' => 'nullable|boolean',
        ]);

        // IMPLEMENTED: Update di Supabase PostgreSQL
        $jobPosting = JobPosting::where('id', $id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $updateData = [
            'job_category_id' => $validated['job_category_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title'], $jobPosting->id),
            'department' => $validated['department'],
            'location' => $validated['location'],
            'job_type' => $validated['job_type'],
            // gaji hanya ditimpa bila dikirim (form edit tidak mengirimnya; sebelumnya setiap edit menghapus gaji)
            'salary_min' => $request->has('salary_min') ? ($validated['salary_min'] ?? null) : $jobPosting->salary_min,
            'salary_max' => $request->has('salary_max') ? ($validated['salary_max'] ?? null) : $jobPosting->salary_max,
            'salary_currency' => $request->has('salary_currency') ? ($validated['salary_currency'] ?? 'IDR') : $jobPosting->salary_currency,
            'description' => $validated['description'],
            'responsibilities' => $validated['responsibilities'],
            'qualifications' => $validated['qualifications'],
            'benefits' => $validated['benefits'] ?? null,
            'skills' => array_values($validated['skills']),
            'sdg_alignment' => isset($validated['sdg_alignment']) ? array_values($validated['sdg_alignment']) : null,
            'impact_metrics' => $validated['impact_metrics'] ?? null,
            'success_criteria' => $validated['success_criteria'] ?? null,
            'status' => $validated['status'] ?? $jobPosting->status,
            'allow_guest_applications' => $validated['allow_guest_applications'] ?? false,
        ];

        // Update published_at jika status berubah ke posted
        if ($validated['status'] === 'posted' && $jobPosting->status !== 'posted') {
            $updateData['published_at'] = now();
        }

        $jobPosting->update($updateData);

        return redirect()->route('company.jobs.show', $id)
            ->with('success', 'Lowongan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $company = $user->company;

        try {
            // IMPLEMENTED: Hapus job posting dari Supabase (soft delete)
            $jobPosting = JobPosting::where('id', $id)
                ->where('company_id', $company->id)
                ->firstOrFail();

            $jobPosting->delete();

            // Return JSON response for AJAX request
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lowongan berhasil dihapus!'
                ]);
            }

            return redirect()->route('company.jobs.index')
                ->with('success', 'Lowongan berhasil dihapus!');
        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => safe_error_message('Gagal menghapus lowongan: ', $e)
                ], 500);
            }

            return redirect()->route('company.jobs.index')
                ->with('error', 'Gagal menghapus lowongan!');
        }
    }

    public function updateLinkedin(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;

        // Validasi input
        $validated = $request->validate([
            'linkedin_url' => 'nullable|url',
            'sdg_goals' => 'nullable|array',
            'sdg_goals.*' => 'integer|min:1|max:17',
        ]);

        // IMPLEMENTED: Update LinkedIn URL dan required SDGs di Supabase
        $jobPosting = JobPosting::where('id', $id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $jobPosting->update([
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'required_sdgs' => isset($validated['sdg_goals']) ? array_map('intval', array_values($validated['sdg_goals'])) : null,
        ]);

        return redirect()->route('company.jobs.show', $id)
            ->with('success', 'LinkedIn reference dan SDG requirements berhasil diperbarui!');
    }

    /**
     * slug unik dari judul: 'judul', lalu 'judul-2', 'judul-3', … (lowongan yang sudah dihapus ikut dihitung karena
     * kolom slug unik di database). $ignoreId = lowongan yang sedang diubah.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($title), 240, '') ?: 'lowongan';
        $slug = $base;
        $n = 2;

        while (JobPosting::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    /**
     * phase 23: urai rentang gaji bebas dari wizard, mis. 'Rp 3.000.000 - Rp 4.500.000 per bulan', 'Rp 3,5 juta',
     * '2 jt - 3 jt / proyek'. angka pertama = min, kedua = max (satu angka = min dan max sama).
     * tidak dapat diurai -> semua null (lowongan tetap tersimpan seperti sebelumnya).
     */
    private function parseSalaryRange(?string $text): array
    {
        $result = ['min' => null, 'max' => null, 'currency' => null, 'period' => null];
        $text = trim((string) $text);
        if ($text === '') {
            return $result;
        }

        $lower = mb_strtolower($text);
        preg_match_all('/(\d+(?:[.,]\d+)*)\s*(juta|jt|ribu|rb|k)?(?![a-z])/u', $lower, $matches, PREG_SET_ORDER);

        $amounts = [];
        foreach ($matches as $match) {
            $number = $match[1];
            $unit = $match[2] ?? '';
            if (preg_match('/^\d{1,3}([.,]\d{3})+$/', $number)) {
                // pemisah ribuan: 3.000.000 atau 3,000,000
                $value = (float) str_replace(['.', ','], '', $number);
            } else {
                // desimal: 3,5 atau 3.5
                $value = (float) str_replace(',', '.', $number);
            }
            $value *= match ($unit) {
                'juta', 'jt' => 1000000,
                'ribu', 'rb', 'k' => 1000,
                default => 1,
            };
            if ($value > 0) {
                $amounts[] = $value;
            }
        }

        // angka kecil (mis. '3 bulan') diabaikan bila ada nominal >= 1.000 di teks yang sama
        if (max($amounts ?: [0]) >= 1000) {
            $amounts = array_values(array_filter($amounts, fn ($value) => $value >= 1000));
        }

        if (!$amounts) {
            return $result;
        }

        $result['min'] = $amounts[0];
        $result['max'] = $amounts[1] ?? $amounts[0];
        if ($result['max'] < $result['min']) {
            [$result['min'], $result['max']] = [$result['max'], $result['min']];
        }
        $result['currency'] = preg_match('/usd|\$/', $lower) ? 'USD' : 'IDR';
        // nilai salary_period yang sudah dipakai data: monthly, project
        $result['period'] = match (true) {
            (bool) preg_match('/bulan|month/', $lower) => 'monthly',
            (bool) preg_match('/proyek|project/', $lower) => 'project',
            default => null,
        };

        return $result;
    }
}
