<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SavedTalent;
use App\Models\Province;
use App\Models\Student;
use App\Models\University;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * TalentController - Browse and Manage Talents
 *
 * IMPLEMENTED: Semua operasi data langsung dari Supabase PostgreSQL
 * TIDAK ADA dummy data lagi
 */
class TalentController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // phase 22: semua filter dijalankan di server sehingga jumlah hasil = jumlah mahasiswa yang cocok.
        // nama parameter lama tetap (skills[], sdg_alignment[], location, verified_only, per_page, page);
        // baru: search, university_id, major, province_id. pool tidak diperluas (D-08).
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'skills' => array_values(array_filter((array) $request->get('skills', []), fn ($skill) => is_string($skill) && $skill !== '')),
            'sdg_alignment' => array_values(array_unique(array_filter(
                array_map('intval', (array) $request->get('sdg_alignment', [])),
                fn ($id) => $id >= 1 && $id <= 17
            ))),
            'university_id' => (int) $request->get('university_id') ?: null,
            'major' => trim((string) $request->get('major', '')),
            'province_id' => (int) $request->get('province_id') ?: null,
            'location' => trim((string) $request->get('location', '')),
            'verified_only' => $request->boolean('verified_only'),
        ];

        // pool talenta (aturan yang sudah ada, D-05/D-08): hanya mahasiswa yang punya minimal 1 proyek
        $pool = fn () => User::where('user_type', 'student')
            ->whereHas('student')
            ->whereHas('student.projects');

        $poolTotal = $pool()->count();
        $talentsQuery = $pool();

        if ($filters['search'] !== '') {
            $like = $this->likePattern($filters['search']);
            $talentsQuery->whereHas('student', function ($query) use ($like) {
                $query->where(function ($match) use ($like) {
                    $match->whereRaw("LOWER(first_name || ' ' || last_name) LIKE ? ESCAPE '!'", [$like])
                        ->orWhereRaw("LOWER(major) LIKE ? ESCAPE '!'", [$like])
                        ->orWhereHas('university', fn ($university) => $university->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like]));
                });
            });
        }

        if (!empty($filters['skills'])) {
            $talentsQuery->whereHas('student', function ($query) use ($filters) {
                foreach ($filters['skills'] as $skill) {
                    $query->whereJsonContains('skills', $skill);
                }
            });
        }

        // sdg = sdg masalah dari proyek yang sudah selesai (sumber yang sama dengan label pada baris)
        if (!empty($filters['sdg_alignment'])) {
            $talentsQuery->whereHas('student.projects', function ($project) use ($filters) {
                $project->where('status', 'completed')->whereHas('problem', function ($problem) use ($filters) {
                    $problem->where(function ($any) use ($filters) {
                        foreach ($filters['sdg_alignment'] as $sdgId) {
                            $any->orWhereJsonContains('sdg_categories', $sdgId)
                                ->orWhereJsonContains('sdg_categories', (string) $sdgId);
                        }
                    });
                });
            });
        }

        if ($filters['university_id']) {
            $talentsQuery->whereHas('student', fn ($query) => $query->where('university_id', $filters['university_id']));
        }

        if ($filters['major'] !== '') {
            $talentsQuery->whereHas('student', fn ($query) => $query->where('major', $filters['major']));
        }

        // lokasi = kabupaten/kota dan provinsi kampus (tabel students tidak punya kolom lokasi)
        if ($filters['province_id']) {
            $talentsQuery->whereHas('student.university', fn ($query) => $query->where('province_id', $filters['province_id']));
        }

        if ($filters['location'] !== '') {
            $like = $this->likePattern($filters['location']);
            $talentsQuery->whereHas('student.university', function ($query) use ($like) {
                $query->where(function ($match) use ($like) {
                    $match->whereHas('regency', fn ($regency) => $regency->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like]))
                        ->orWhereHas('province', fn ($province) => $province->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like]));
                });
            });
        }

        if ($filters['verified_only']) {
            $talentsQuery->whereNotNull('email_verified_at');
        }

        $talents = $talentsQuery->with(['student.university.regency', 'student.university.province', 'student.projects.problem'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // tie-breaker agar halaman tidak tumpang tindih (D-05)
            ->paginate((int) $request->get('per_page', 12))
            ->withQueryString();

        // Transform data untuk view
        $talents->getCollection()->transform(function ($talent) {
            $student = $talent->student;
            $university = $student?->university;

            // sdg dari proyek yang sudah selesai; nama bahasa indonesia dari sdg_label() (satu daftar)
            $sdgBadges = [];
            foreach (($student?->projects ?? collect())->where('status', 'completed') as $project) {
                $categories = $project->problem?->sdg_categories;

                // Handle jika masih string JSON
                if (is_string($categories)) {
                    $categories = json_decode($categories, true) ?? [];
                }

                foreach ((array) $categories as $sdgId) {
                    $sdgId = (int) $sdgId;
                    if ($sdgId >= 1 && $sdgId <= 17 && !isset($sdgBadges[$sdgId])) {
                        $sdgBadges[$sdgId] = ['id' => $sdgId, 'name' => sdg_label($sdgId)];
                    }
                }
            }
            ksort($sdgBadges);

            // lokasi kampus asli; null bila tidak ada (bukan lagi 'Indonesia' sebagai pengganti)
            $location = collect([$university?->regency?->name, $university?->province?->name])->filter()->implode(', ');

            return [
                'id' => $talent->id,
                'username' => $talent->username,
                'name' => $talent->display_name,
                'title' => $student?->major ?: null,
                'university' => $university?->name,
                'location' => $location !== '' ? $location : null,
                'verified' => !is_null($talent->email_verified_at),
                'sdg_badges' => array_values($sdgBadges),
                'skills' => is_array($student?->skills) ? array_values($student->skills) : [],
                'projects_count' => $student?->projects?->count() ?? 0,
            ];
        });

        // Return JSON for AJAX requests (load more functionality)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'talents' => $talents->items(),
                'hasMore' => $talents->hasMorePages(),
                'currentPage' => $talents->currentPage(),
                'total' => $talents->total(),
            ]);
        }

        // pilihan filter hanya dari data pool yang nyata
        $poolUniversityIds = Student::whereHas('projects')->select('university_id');
        $universityOptions = University::whereIn('id', $poolUniversityIds)->orderBy('name')->pluck('name', 'id')->all();
        $provinceOptions = Province::whereIn('id', University::whereIn('id', Student::whereHas('projects')->select('university_id'))->select('province_id'))
            ->orderBy('name')->pluck('name', 'id')->all();
        $majorOptions = Student::whereHas('projects')->whereNotNull('major')->where('major', '!=', '')
            ->distinct()->orderBy('major')->pluck('major')->all();
        $skillOptions = Student::whereHas('projects')->whereNotNull('skills')->pluck('skills')
            ->flatMap(fn ($skills) => is_array($skills) ? $skills : [])
            ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')
            ->map(fn ($skill) => trim($skill))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
        $sdgOptions = collect(range(1, 17))->mapWithKeys(fn ($id) => [$id => 'SDG ' . $id . ' · ' . sdg_label($id)])->all();

        // status simpan dari database (bukan localStorage)
        $savedIds = $company
            ? SavedTalent::where('company_id', $company->id)
                ->whereIn('user_id', collect($talents->items())->pluck('id'))
                ->pluck('user_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $totalTalents = $talents->total();
        $activeFilters = count($filters['sdg_alignment']) + count($filters['skills'])
            + (int) (bool) $filters['university_id'] + (int) ($filters['major'] !== '')
            + (int) (bool) $filters['province_id'] + (int) ($filters['location'] !== '') + (int) $filters['verified_only'];
        $isFiltered = $activeFilters > 0 || $filters['search'] !== '';

        return view('company.talents.index', compact(
            'company',
            'talents',
            'filters',
            'sdgOptions',
            'universityOptions',
            'majorOptions',
            'provinceOptions',
            'skillOptions',
            'savedIds',
            'totalTalents',
            'poolTotal',
            'activeFilters',
            'isFiltered'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();
        $company = $user->company;

        // IMPLEMENTED: Ambil data talent dari Supabase berdasarkan id
        $talent = User::where('user_type', 'student')
            ->where('id', $id)
            ->with(['student'])
            ->firstOrFail();

        // Check if talent is saved by company
        $isSaved = SavedTalent::where('company_id', $company->id)
            ->where('user_id', $id)
            ->exists();

        return view('company.talents.show', compact('talent', 'company', 'isSaved'));
    }

    public function saved(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('home')
                ->with('error', 'profil perusahaan tidak ditemukan');
        }

        // IMPLEMENTED: Ambil data saved talents dari Supabase
        $savedTalents = SavedTalent::where('company_id', $company->id)
            ->with('user.student.university.regency', 'user.student.university.province')
            ->orderBy('saved_at', 'desc')
            ->orderBy('id', 'desc') // tie-breaker (D-05)
            ->get();

        // phase 22: folder = kolom category yang tersimpan; catatan = kolom notes (hanya dibaca, tidak ada endpoint ubah)
        $savedRows = $savedTalents->filter(fn ($savedTalent) => $savedTalent->user)->map(function ($savedTalent) {
            $user = $savedTalent->user;
            $student = $user->student ?? null;
            $university = $student?->university;
            $location = collect([$university?->regency?->name, $university?->province?->name])->filter()->implode(', ');

            return [
                'id' => $user->id,
                'name' => $user->display_name,
                'username' => $user->username,
                'title' => $student?->major ?: null,
                'university' => $university?->name,
                'location' => $location !== '' ? $location : null,
                'folder' => filled($savedTalent->category) ? $savedTalent->category : null,
                'notes' => filled($savedTalent->notes) ? $savedTalent->notes : null,
                'saved_at' => $savedTalent->saved_at,
            ];
        })->values();

        // folder bernama urut abjad; 'Tanpa folder' terakhir
        $folders = $savedRows->groupBy(fn ($row) => $row['folder'] ?? '')
            ->map(fn ($rows, $name) => ['name' => $name !== '' ? $name : null, 'count' => $rows->count()])
            ->sortBy(fn ($folder) => [$folder['name'] === null ? 1 : 0, mb_strtolower((string) $folder['name'])])
            ->values()->all();

        $totalSavedTalents = $savedRows->count();

        return view('company.talents.saved', compact(
            'company',
            'savedRows',
            'folders',
            'totalSavedTalents'
        ));
    }

    /**
     * IMPLEMENTED: Toggle save/unsave talent
     * Data langsung ke Supabase PostgreSQL
     */
    public function toggleSave(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;

        // Check if already saved
        $savedTalent = SavedTalent::where('company_id', $company->id)
            ->where('user_id', $id)
            ->first();

        if ($savedTalent) {
            // Unsave
            $savedTalent->delete();
            return response()->json([
                'success' => true,
                'action' => 'unsaved',
                'message' => 'Talent removed from saved list'
            ]);
        } else {
            // Save
            SavedTalent::create([
                'company_id' => $company->id,
                'user_id' => $id,
                'category' => $request->input('category', null),
                'notes' => $request->input('notes', null),
                'saved_at' => now(),
            ]);
            return response()->json([
                'success' => true,
                'action' => 'saved',
                'message' => 'Talent added to saved list'
            ]);
        }
    }

    /**
     * IMPLEMENTED: Contact talent
     * Send message or interview request
     */
    public function contact(Request $request, $id)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'type' => 'required|in:message,interview_request'
        ]);

        $user = Auth::user();
        $company = $user->company;

        // IMPLEMENTED: Save message to database
        // This would typically send a notification to the talent
        DB::connection('pgsql')->table('messages')->insert([
            'from_user_id' => $user->id,
            'to_user_id' => $id,
            'company_id' => $company->id,
            'message' => $validated['message'],
            'type' => $validated['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully'
        ]);
    }

    /**
     * Compare talents side by side
     * IMPLEMENTED: Data dari Supabase PostgreSQL
     */
    public function compare(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Get talent IDs from query string
        $ids = explode(',', $request->get('ids', ''));
        $ids = array_filter($ids, 'is_numeric');

        if (count($ids) < 2 || count($ids) > 3) {
            return redirect()->route('company.talents.index')
                ->with('error', 'Pilih 2-3 talenta untuk dibandingkan');
        }

        // Get talents data with all necessary relationships
        $talents = User::where('user_type', 'student')
            ->whereIn('id', $ids)
            ->with(['student.university', 'student.projects'])
            ->get();

        if ($talents->count() < 2) {
            return redirect()->route('company.talents.index')
                ->with('error', 'Talenta tidak ditemukan');
        }

        return view('company.talents.compare', compact('talents', 'company'));
    }

    /**
     * Export talents list to CSV
     * IMPLEMENTED: Data dari Supabase PostgreSQL
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        // Apply same filters as index page
        $filters = [
            'skills' => $request->get('skills', []),
            'sdg_alignment' => $request->get('sdg_alignment', []),
            'location' => $request->get('location', ''),
            'verified_only' => $request->get('verified_only', false),
        ];

        $talentsQuery = User::where('user_type', 'student')
            ->whereHas('student');

        // Apply filters (same as index method)
        if (!empty($filters['skills'])) {
            $talentsQuery->whereHas('student', function ($query) use ($filters) {
                foreach ($filters['skills'] as $skill) {
                    $query->whereJsonContains('skills', $skill);
                }
            });
        }

        if (!empty($filters['location'])) {
            $talentsQuery->whereHas('student', function ($query) use ($filters) {
                $query->where('location', 'ILIKE', '%' . $filters['location'] . '%');
            });
        }

        if ($filters['verified_only']) {
            $talentsQuery->whereNotNull('email_verified_at');
        }

        $talents = $talentsQuery->with(['student.university', 'student.projects'])->get();

        $filename = 'talents_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($talents) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Title', 'Location', 'Skills', 'Verified', 'Projects Count']);

            foreach ($talents as $talent) {
                $profile = $talent->profile ?? null;
                $skills = is_array($profile->skills ?? null) ? implode(', ', $profile->skills) : 'N/A';
                $location = $profile && $profile->university ? $profile->university->city : 'N/A';
                $projectsCount = $profile && $profile->projects ? $profile->projects->count() : 0;

                fputcsv($file, [
                    $talent->display_name,
                    $talent->email,
                    $profile->major ?? 'N/A',
                    $location,
                    $skills,
                    $talent->email_verified_at ? 'Yes' : 'No',
                    $projectsCount,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export saved talents to CSV
     * IMPLEMENTED: Data dari Supabase PostgreSQL
     */
    public function exportSaved(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        $savedTalents = SavedTalent::where('company_id', $company->id)
            ->with('user.student.university', 'user.student.projects')
            ->get();

        $filename = 'saved_talents_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($savedTalents) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Title', 'Location', 'Category', 'Notes', 'Saved At']);

            foreach ($savedTalents as $savedTalent) {
                $user = $savedTalent->user;
                $profile = $user->profile ?? null;
                $location = $profile && $profile->university ? $profile->university->city : 'N/A';

                fputcsv($file, [
                    $user->display_name,
                    $user->email,
                    $profile->major ?? 'N/A',
                    $location,
                    $savedTalent->category ?? 'N/A',
                    $savedTalent->notes ?? 'N/A',
                    $savedTalent->saved_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * pola LIKE yang aman: %, _ dan ! di-escape dengan '!' (dipakai bersama ESCAPE '!', jalan di pgsql dan sqlite)
     */
    private function likePattern(string $term): string
    {
        return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)) . '%';
    }
}
