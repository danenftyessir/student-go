<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SupabaseService - Service untuk handle SEMUA operasi database dan storage ke Supabase
 *
 * PENTING: SEMUA data CRUD WAJIB langsung ke Supabase PostgreSQL
 * PENTING: SEMUA file foto/gambar WAJIB disimpan di Supabase Storage
 * TIDAK ADA data yang disimpan di local database/storage
 */
class SupabaseService
{
    protected $supabaseUrl;
    protected $supabaseKey;
    protected $supabaseServiceKey;
    protected $supabaseStorageUrl;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url');
        $this->supabaseKey = config('services.supabase.anon_key');
        $this->supabaseServiceKey = config('services.supabase.service_key');
        $this->supabaseStorageUrl = config('services.supabase.storage_url');
    }

    // ========================================================================
    // DATABASE OPERATIONS - DIRECT TO SUPABASE POSTGRESQL
    // ========================================================================

    /**
     * Select data dari Supabase table
     */
    public function select($table, $columns = '*', $conditions = [])
    {
        try {
            $query = DB::connection('pgsql')->table($table)->select($columns);

            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }

            return $query->get();
        } catch (\Exception $e) {
            Log::error("Supabase select error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Insert data ke Supabase table
     */
    public function insert($table, $data)
    {
        try {
            return DB::connection('pgsql')->table($table)->insertGetId($data);
        } catch (\Exception $e) {
            Log::error("Supabase insert error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update data di Supabase table
     */
    public function update($table, $id, $data)
    {
        try {
            return DB::connection('pgsql')->table($table)
                ->where('id', $id)
                ->update($data);
        } catch (\Exception $e) {
            Log::error("Supabase update error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete data dari Supabase table
     */
    public function delete($table, $id)
    {
        try {
            return DB::connection('pgsql')->table($table)
                ->where('id', $id)
                ->delete();
        } catch (\Exception $e) {
            Log::error("Supabase delete error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find single record dari Supabase table
     */
    public function find($table, $id)
    {
        try {
            return DB::connection('pgsql')->table($table)
                ->where('id', $id)
                ->first();
        } catch (\Exception $e) {
            Log::error("Supabase find error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Query builder untuk query kompleks ke Supabase
     */
    public function query($table)
    {
        return DB::connection('pgsql')->table($table);
    }

    // ========================================================================
    // STORAGE OPERATIONS - DIRECT TO SUPABASE STORAGE
    // ========================================================================

    /**
     * Upload file ke Supabase Storage
     * WAJIB: Semua foto/gambar HARUS disimpan di Supabase Storage
     *
     * @param string $bucket Nama bucket (company_logos, job_images, talent_avatars, etc)
     * @param string $filePath Path file di bucket
     * @param mixed $file File content atau UploadedFile
     * @return string Public URL dari file yang diupload
     */
    public function uploadFile($bucket, $filePath, $file)
    {
        try {
            // semua file ada di satu bucket (SUPABASE_BUCKET); $bucket dipakai sebagai folder di dalamnya.
            // lewat SupabaseStorageService (REST), bukan disk s3 'supabase' yang tidak punya kredensial S3 valid
            $fullPath = $this->storagePath($bucket, $filePath);

            if (is_string($file)) {
                // jika file adalah string (isi file): tulis ke file sementara agar bisa diunggah
                $tmp = tempnam(sys_get_temp_dir(), 'sg');
                file_put_contents($tmp, $file);
                $file = new UploadedFile($tmp, basename($filePath), null, null, true);
            }

            $path = $this->storage()->uploadFile($file, $fullPath);

            if (!$path) {
                throw new \Exception("Upload ke storage gagal: {$fullPath}");
            }

            // Return public URL
            return $this->getPublicUrl($bucket, $filePath);
        } catch (\Exception $e) {
            Log::error("Supabase upload file error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete file dari Supabase Storage
     */
    public function deleteFile($bucket, $filePath)
    {
        try {
            return $this->storage()->delete($this->storagePath($bucket, $filePath));
        } catch (\Exception $e) {
            Log::error("Supabase delete file error: " . $e->getMessage());
            // Don't throw error for delete failures - just log it
            return false;
        }
    }

    /**
     * Get public URL dari file di Supabase Storage
     */
    public function getPublicUrl($bucket, $filePath)
    {
        return $this->storage()->getPublicUrl($this->storagePath($bucket, $filePath));
    }

    /**
     * Generate signed URL untuk private files
     */
    public function getSignedUrl($bucket, $filePath, $expiresIn = 3600)
    {
        try {
            return $this->storage()->createSignedUrl($this->storagePath($bucket, $filePath), (int) $expiresIn);
        } catch (\Exception $e) {
            Log::error("Supabase signed URL error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * List files dalam bucket
     */
    public function listFiles($bucket, $prefix = '')
    {
        try {
            return $this->storage()->listFiles($prefix ? "{$bucket}/{$prefix}" : $bucket);
        } catch (\Exception $e) {
            Log::error("Supabase list files error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * path di bucket utama: "<folder>/<file>"
     */
    protected function storagePath($bucket, $filePath): string
    {
        return trim($bucket, '/') . '/' . ltrim($filePath, '/');
    }

    protected function storage(): SupabaseStorageService
    {
        return app(SupabaseStorageService::class);
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Execute raw SQL query ke Supabase PostgreSQL
     */
    public function raw($sql, $bindings = [])
    {
        try {
            return DB::connection('pgsql')->select($sql, $bindings);
        } catch (\Exception $e) {
            Log::error("Supabase raw query error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        DB::connection('pgsql')->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        DB::connection('pgsql')->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        DB::connection('pgsql')->rollBack();
    }

    /**
     * Check if Supabase connection is alive
     */
    public function healthCheck()
    {
        try {
            DB::connection('pgsql')->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error("Supabase health check failed: " . $e->getMessage());
            return false;
        }
    }
}
