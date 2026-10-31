<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * phase 1 (J5, D-06): form edit profil perusahaan membaca dan menulis kolom-kolom ini,
 * tetapi tabel companies tidak memilikinya sehingga setiap simpan gagal (SQLSTATE 42703).
 * migrasi ADDITIF: hanya menambah kolom nullable, tidak mengubah atau menghapus data
 */
return new class extends Migration
{
    private array $columns = [
        'tagline' => 255,
        'email' => 255,
        'company_size' => 50,
        'location' => 255,
        'postal_code' => 20,
        'linkedin' => 255,
        'twitter' => 255,
        'facebook' => 255,
        'instagram' => 255,
    ];

    public function up(): void
    {
        foreach ($this->columns as $column => $length) {
            if (!Schema::hasColumn('companies', $column)) {
                Schema::table('companies', function (Blueprint $table) use ($column, $length) {
                    $table->string($column, $length)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->columns) as $column) {
            if (Schema::hasColumn('companies', $column)) {
                Schema::table('companies', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
