<?php

namespace Tests\Unit;

use danielme85\Geoip2\Geoip2ServiceProvider;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class DownloaderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [Geoip2ServiceProvider::class];
    }

    protected function getEnvironmentSetup($app): void
    {
        $app['config']->set('geoip2.folder', 'app/geoip2-test');
        $app['config']->set('geoip2.filename', 'GeoLite2-City.mmdb');
        $app['config']->set('geoip2.downloadUrl', 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&suffix=tar.gz');
        $app['config']->set('geoip2.license', 'test-license-key');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupTestStorage();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestStorage();
        parent::tearDown();
    }

    private function cleanupTestStorage(): void
    {
        $folder = storage_path('app/geoip2-test');
        if (is_dir($folder)) {
            $this->deleteDirectory($folder);
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (array_diff(scandir($path), ['.', '..']) as $entry) {
            $full = "{$path}/{$entry}";
            is_dir($full) ? $this->deleteDirectory($full) : unlink($full);
        }
        rmdir($path);
    }

    public function test_download_command_is_registered(): void
    {
        $this->assertTrue(
            array_key_exists('geoip:download', $this->app->make('Illuminate\Contracts\Console\Kernel')->all())
        );
    }

    public function test_download_fails_gracefully_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response('Unauthorized', 401),
        ]);

        $this->artisan('geoip:download')
            ->expectsOutput('Download failed (HTTP 401).')
            ->assertExitCode(1);
    }
}
