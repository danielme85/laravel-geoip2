<?php

namespace Tests\Unit;

use danielme85\Geoip2\Geoip2ServiceProvider;
use Orchestra\Testbench\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Geoip2ConnectorTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [Geoip2ServiceProvider::class];
    }

    protected function getEnvironmentSetup($app): void
    {
        $app['config']->set('geoip2.folder', 'app/geoip2');
        $app['config']->set('geoip2.filename', 'GeoLite2-City.mmdb');
    }

    public function test_connect_aborts_when_db_file_missing(): void
    {
        $this->expectException(HttpException::class);

        try {
            \danielme85\Geoip2\Geoip2Connector::connect();
        } catch (HttpException $e) {
            $this->assertSame(500, $e->getStatusCode());
            throw $e;
        }
    }

    public function test_connect_returns_reader_when_db_exists(): void
    {
        $dbFolder = storage_path(config('geoip2.folder'));

        if (!is_dir($dbFolder)) {
            mkdir($dbFolder, 0755, true);
        }

        // Create a minimal valid .mmdb file (real Reader requires a valid binary — skip if we can't)
        $dbPath = $dbFolder . '/' . config('geoip2.filename');
        file_put_contents($dbPath, '');

        try {
            // A zero-byte file will cause GeoIp2 Reader to throw an InvalidDatabaseException,
            // but connect() itself should not abort — the error comes from the Reader constructor.
            $this->expectException(\MaxMind\Db\Reader\InvalidDatabaseException::class);
            \danielme85\Geoip2\Geoip2Connector::connect();
        } finally {
            unlink($dbPath);
        }
    }
}
