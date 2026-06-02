<?php

namespace Tests\Unit;

use danielme85\Geoip2\Geoip2Connector;
use danielme85\Geoip2\Geoip2ServiceProvider;
use Orchestra\Testbench\TestCase;

class Geoip2ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [Geoip2ServiceProvider::class];
    }

    public function test_service_provider_registers_reader_binding(): void
    {
        $this->assertTrue($this->app->bound('Reader'));
    }

    public function test_reader_binding_resolves_to_connector(): void
    {
        $instance = $this->app->make('Reader');
        $this->assertInstanceOf(Geoip2Connector::class, $instance);
    }

    public function test_config_is_merged(): void
    {
        $this->assertNotNull(config('geoip2.downloadUrl'));
        $this->assertNotNull(config('geoip2.folder'));
        $this->assertNotNull(config('geoip2.filename'));
        $this->assertNotNull(config('geoip2.localhost'));
    }

}
