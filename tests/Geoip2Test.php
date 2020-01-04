<?php
/**
 * Created by PhpStorm.
 * User: danielme85
 * Date: 10/14/17
 * Time: 10:08 PM
 */


class Geoip2Test extends Orchestra\Testbench\TestCase
{

    protected $downloadFile, $downloadUrl, $dbFilePath;

    protected function getPackageProviders($app)
    {
        return [
            'danielme85\Geoip2\Geoip2ServiceProvider'
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(__DIR__);
        $app['config']->set('geoip2.downloadUrl', 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz');
        $app['config']->set('geoip2.tempFile', 'GeoLite2-City.mmdb.gz');
        $app['config']->set('geoip2.dbName', 'GeoLite2-City.mmdb');
    }

    /**
     * Test the geopip2 maxmind database download.
     *
     * @return void
     */
    public function testDatabaseDownload()
    {
        $downloader = new \danielme85\Geoip2\Commands\Downloader();
        $downloader->handle();
    }

    /**
     * Test the geopip2 maxmind database download.
     *
     * @return void
     */
    public function testIPLookup() {
        $reader = danielme85\Geoip2\Facade\Reader::connect();
        $ip = '8.8.8.8'; //Google open dns
        $geodata = $reader->city($ip)->jsonSerialize();
        $this->assertArrayHasKey('continent', $geodata);
    }

}