<?php

namespace danielme85\Geoip2;

use danielme85\Geoip2\Commands\Downloader;
use Illuminate\Support\ServiceProvider;

class Geoip2ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([Downloader::class]);
        }

        $this->mergeConfigFrom(__DIR__ . '/config/geoip2.php', 'geoip2');

        $this->publishes([
            __DIR__ . '/config/geoip2.php' => config_path('geoip2.php'),
        ]);
    }

    public function register(): void
    {
        $this->app->bind('Reader', fn() => new Geoip2Connector());
    }
}
