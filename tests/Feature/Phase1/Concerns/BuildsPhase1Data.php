<?php

namespace Tests\Feature\Phase1\Concerns;

use App\Models\Company;
use App\Models\Institution;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * fixture kecil untuk test phase 1 (sqlite in-memory)
 */
trait BuildsPhase1Data
{
    protected int $provinceId;
    protected int $regencyId;
    protected int $universityId;
    private int $seq = 0;

    protected function seedRegion(): void
    {
        $this->provinceId = DB::table('provinces')->insertGetId(['name' => 'Jawa Barat', 'code' => '32']);
        $this->regencyId = DB::table('regencies')->insertGetId(['province_id' => $this->provinceId, 'name' => 'Kota Bandung', 'code' => '3273']);
        $this->universityId = DB::table('universities')->insertGetId([
            'name' => 'Institut Teknologi Bandung', 'code' => 'ITB',
            'province_id' => $this->provinceId, 'regency_id' => $this->regencyId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /**
     * buat mahasiswa; $userName boleh berbeda dari first/last untuk meniru data seed lama
     */
    protected function makeStudent(string $first, string $last, ?string $userName = null, array $studentAttrs = [], array $userAttrs = []): Student
    {
        $this->seq++;
        $username = strtolower($first . $last . $this->seq);

        $user = User::create(array_merge([
            'name' => $userName ?? ($first . ' ' . $last),
            'email' => $username . '@mahasiswa.test',
            'username' => $username,
            'password' => 'rahasia-test',
            'user_type' => 'student',
            'is_active' => true,
            'email_verified_at' => now(),
        ], $userAttrs));

        return Student::create(array_merge([
            'user_id' => $user->id,
            'first_name' => $first,
            'last_name' => $last,
            'university_id' => $this->universityId,
            'major' => 'Teknik Informatika',
            'nim' => (string) (1000000000 + $this->seq),
            'semester' => 5,
            'phone' => '+62812000' . str_pad((string) $this->seq, 5, '0', STR_PAD_LEFT),
        ], $studentAttrs))->load('user');
    }

    protected function makeInstitution(string $type = 'Dinas Kesehatan'): Institution
    {
        $this->seq++;
        $user = User::create([
            'name' => 'Dinas ' . $this->seq,
            'email' => 'dinas' . $this->seq . '@instansi.test',
            'username' => 'dinas' . $this->seq,
            'password' => 'rahasia-test',
            'user_type' => 'institution',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return Institution::create([
            'user_id' => $user->id,
            'name' => 'Dinas Kesehatan Kota Bandung ' . $this->seq,
            'type' => $type,
            'address' => 'Jl. Test',
            'province_id' => $this->provinceId,
            'regency_id' => $this->regencyId,
            'email' => 'dinas' . $this->seq . '@instansi.test',
            'phone' => '0221234567',
            'pic_name' => 'PIC',
            'pic_position' => 'Kepala',
            'is_verified' => true,
        ]);
    }

    protected function makeCompany(array $attrs = []): Company
    {
        $this->seq++;
        $user = User::create([
            'name' => 'PT Test ' . $this->seq,
            'email' => 'hr' . $this->seq . '@perusahaan.test',
            'username' => 'pttest' . $this->seq,
            'password' => 'rahasia-test',
            'user_type' => 'company',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return Company::create(array_merge([
            'user_id' => $user->id,
            'name' => 'PT Test ' . $this->seq,
            'industry' => 'Technology',
        ], $attrs));
    }

    /**
     * buat banyak problem terbuka dengan created_at yang SAMA (meniru seeder batch)
     */
    protected function makeOpenProblems(int $institutionId, int $count, ?Carbon $createdAt = null): array
    {
        $createdAt ??= Carbon::parse('2026-10-01 10:00:00');
        $ids = [];
        for ($i = 1; $i <= $count; $i++) {
            $ids[] = DB::table('problems')->insertGetId([
                'institution_id' => $institutionId,
                'title' => 'Masalah ' . $i,
                'description' => 'Deskripsi ' . $i,
                'province_id' => $this->provinceId,
                'regency_id' => $this->regencyId,
                'sdg_categories' => json_encode([1]),
                'required_students' => 3,
                'required_skills' => json_encode([]),
                'start_date' => '2026-12-01',
                'end_date' => '2027-02-01',
                'application_deadline' => '2026-11-20',
                'duration_months' => 2,
                'status' => 'open',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        return $ids;
    }

    protected function makeProject(Student $student, int $institutionId, int $problemId): void
    {
        $applicationId = DB::table('applications')->insertGetId([
            'student_id' => $student->id,
            'problem_id' => $problemId,
            'motivation' => 'Motivasi',
            'status' => 'accepted',
            'applied_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('projects')->insert([
            'application_id' => $applicationId,
            'student_id' => $student->id,
            'problem_id' => $problemId,
            'institution_id' => $institutionId,
            'title' => 'Proyek',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makeFriendship(Student $a, Student $b, string $status = 'accepted'): void
    {
        DB::table('friends')->insert([
            'requester_id' => $a->id,
            'receiver_id' => $b->id,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
