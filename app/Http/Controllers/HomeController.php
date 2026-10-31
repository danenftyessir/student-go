<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Project;
use App\Support\RegionCentroids;
use Illuminate\Support\Str;
use Throwable;

class HomeController extends Controller
{
    /**
     * tampilkan homepage
     *
     * semua angka dan data di beranda berasal dari query nyata (phase 4, guardrail 2).
     * bila database tidak terjangkau, $home bernilai null dan view menyembunyikan blok data
     * alih-alih menampilkan angka nol.
     */
    public function index()
    {
        try {
            $home = $this->loadHomeData();
        } catch (Throwable $e) {
            report($e);
            $home = null;
        }

        return view('home.index', compact('home'));
    }

    /**
     * kumpulkan data publik beranda: masalah dibuka (daftar + peta), bidang sdg, dan hasil proyek
     */
    private function loadHomeData(): array
    {
        // semua masalah yang masih menerima lamaran, tenggat terdekat lebih dulu
        $open = Problem::open()
            ->with(['institution:id,name', 'regency:id,name,code', 'province:id,name,code'])
            ->orderBy('application_deadline')
            ->orderBy('id')
            ->limit(500)
            ->get([
                'id', 'title', 'institution_id', 'province_id', 'regency_id', 'sdg_categories',
                'required_students', 'duration_months', 'difficulty_level', 'is_urgent', 'application_deadline', 'status',
            ]);

        // satu titik per kabupaten/kota; koordinat rujukan dari kode bps, jumlah dari data
        $points = [];
        $provinceTotals = [];
        foreach ($open as $problem) {
            if ($problem->province) {
                $provinceTotals[$problem->province->id] ??= ['id' => $problem->province->id, 'name' => $problem->province->name, 'total' => 0];
                $provinceTotals[$problem->province->id]['total']++;
            }

            $key = (string) ($problem->regency_id ?: 'p'.$problem->province_id);
            if (! isset($points[$key])) {
                $coords = RegionCentroids::forRegency($problem->regency?->code, $problem->province?->code);
                if (! $coords) {
                    continue;
                }
                $points[$key] = [
                    'key' => $key,
                    'name' => $problem->regency?->name ?? $problem->province?->name,
                    'province' => $problem->province?->name,
                    'province_id' => $problem->province_id,
                    'lat' => $coords[0],
                    'lng' => $coords[1],
                ];
            }
        }

        // jumlah masalah dibuka per bidang sdg (satu masalah bisa punya beberapa sdg)
        $sdgCounts = [];
        foreach ($open as $problem) {
            foreach ((array) $problem->sdg_categories as $sdg) {
                $sdgCounts[(int) $sdg] = ($sdgCounts[(int) $sdg] ?? 0) + 1;
            }
        }
        arsort($sdgCounts);
        $sdgs = collect($sdgCounts)->map(fn ($count, $sdg) => [
            'id' => $sdg,
            'label' => self::sdgName($sdg),
            'total' => $count,
        ])->values()->all();

        // data ringkas per masalah untuk peta dan panel lokasi
        $mapProblems = $open->map(fn ($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'institution' => $p->institution?->name,
            'point' => (string) ($p->regency_id ?: 'p'.$p->province_id),
            'province_id' => $p->province_id,
            'sdgs' => array_map('intval', (array) $p->sdg_categories),
            'deadline' => $p->application_deadline?->locale('id')->translatedFormat('j M Y'),
            'urgent' => (bool) $p->is_urgent,
            'url' => route('student.browse-problems.show', $p->id),
        ])->values()->all();

        // hasil proyek yang tercatat (langsung dari tabel projects, bukan angka pemasaran)
        $projects = Project::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $outcomes = array_values(array_filter([
            ['value' => (int) ($projects['completed'] ?? 0), 'label' => 'proyek KKN selesai'],
            ['value' => (int) ($projects['active'] ?? 0), 'label' => 'proyek sedang berjalan'],
            ['value' => Project::distinct()->count('student_id'), 'label' => 'mahasiswa pernah terlibat proyek'],
            ['value' => Project::distinct()->count('institution_id'), 'label' => 'instansi pernah menjalankan proyek'],
        ], fn ($item) => $item['value'] > 0));

        $openTotal = $open->count();

        return [
            'openTotal' => $openTotal,
            'summary' => $openTotal > 0 ? [
                ['value' => $openTotal, 'label' => 'masalah dibuka'],
                ['value' => $open->pluck('institution_id')->unique()->count(), 'label' => 'instansi'],
                ['value' => $open->pluck('regency_id')->filter()->unique()->count(), 'label' => 'kabupaten/kota'],
                ['value' => count($provinceTotals), 'label' => 'provinsi'],
            ] : [],
            'urgentTotal' => $open->where('is_urgent', true)->count(),
            'soonest' => $open->take(5),
            'sdgs' => $sdgs,
            'sdgMax' => $sdgs[0]['total'] ?? 0,
            'provinceTotals' => collect($provinceTotals)->sortByDesc('total')->values()->all(),
            'points' => array_values($points),
            'mapProblems' => $mapProblems,
            'outcomes' => $outcomes,
        ];
    }

    /** nama sdg dalam huruf kalimat ("Kota dan komunitas berkelanjutan") */
    public static function sdgName(int $sdg): string
    {
        return function_exists('sdg_label') ? Str::ucfirst(Str::lower(sdg_label($sdg))) : 'SDG '.$sdg;
    }
}
