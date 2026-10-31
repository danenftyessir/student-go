<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    /**
     * pengaman: banyak model memakai koneksi 'pgsql' secara eksplisit,
     * jadi koneksi itu diarahkan ke sqlite in-memory agar test tidak pernah menyentuh database asli
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $sqlite = $app['config']->get('database.connections.sqlite');
        $sqlite['database'] = ':memory:';

        $app['config']->set('database.connections.pgsql', $sqlite);
        $app['config']->set('database.default', 'pgsql');

        // jangan pernah memakai kredensial supabase asli saat test
        $app['config']->set('services.supabase', ['url' => null, 'anon_key' => null, 'service_key' => null, 'project_id' => null, 'storage_url' => null, 'bucket' => 'test-bucket']);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // hentikan test jika koneksi bukan sqlite in-memory
        if (config('database.connections.pgsql.driver') !== 'sqlite'
            || config('database.connections.pgsql.database') !== ':memory:') {
            $this->fail('test harus memakai sqlite in-memory, bukan database asli');
        }

        // tolak semua request http keluar (supabase, AI, BPS)
        Http::preventStrayRequests();
    }
}
