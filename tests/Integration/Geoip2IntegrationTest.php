<?php

namespace Tests\Integration;

use danielme85\Geoip2\Facade\Reader;
use danielme85\Geoip2\Geoip2ServiceProvider;
use Orchestra\Testbench\TestCase;

class Geoip2IntegrationTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [Geoip2ServiceProvider::class];
    }

    protected function getEnvironmentSetup($app): void
    {
        $app->setBasePath(dirname(__DIR__));

        $envFile = dirname(__DIR__, 2) . '/.env.testing';
        if (file_exists($envFile)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2), '.env.testing');
            $dotenv->load();
        }

        $license = getenv('GEOIP2_LICENSE') ?: env('GEOIP2_LICENSE') ?: '';
        $app['config']->set('geoip2.license', $license);
    }

    public function test_database_download(): void
    {
        if (!config('geoip2.license')) {
            $this->markTestSkipped('GEOIP2_LICENSE env var not set — skipping live download test.');
        }

        $this->artisan('geoip:download')->assertExitCode(0);

        $filePath = storage_path(config('geoip2.folder') . '/' . config('geoip2.filename'));
        $this->assertFileExists($filePath);
    }

    public function test_ip_lookup_returns_continent(): void
    {
        $filePath = storage_path(config('geoip2.folder') . '/' . config('geoip2.filename'));

        if (!file_exists($filePath)) {
            $this->markTestSkipped('GeoLite2 DB not present — run geoip:download first or set GEOIP2_LICENSE.');
        }

        $reader = Reader::connect();
        $geodata = $reader->city('8.8.8.8')->jsonSerialize();

        $this->assertArrayHasKey('continent', $geodata);
        $this->assertArrayHasKey('country', $geodata);
        $this->assertArrayHasKey('location', $geodata);
    }
}
