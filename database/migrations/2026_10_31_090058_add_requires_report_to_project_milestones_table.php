<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * phase 17 (disetujui user 2026-10-31): tenggat laporan. instansi menandai milestone yang wajib
 * disertai laporan; target_date milestone itu menjadi tenggat laporan di dashboard instansi.
 * migrasi ADDITIF: satu kolom boolean default false, tidak mengubah atau menghapus data
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('project_milestones', 'requires_report')) {
            Schema::table('project_milestones', function (Blueprint $table) {
                $table->boolean('requires_report')->default(false)->after('deliverables');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('project_milestones', 'requires_report')) {
            Schema::table('project_milestones', function (Blueprint $table) {
                $table->dropColumn('requires_report');
            });
        }
    }
};
