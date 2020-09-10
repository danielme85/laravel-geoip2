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

        if (file_exists(dirname(__DIR__).'/.env.testing')) {
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env.testing');
            $dotenv->load();
        }

        $hash1 = getenv('HASH1') ?: env('HASH1') ?: '';
        $app['config']->set('geoip2.license', $hash1);
    }

    /**
     * Test the geopip2 maxmind database download.
     *
     * @return void
     */
    public function testDatabaseDownload()
    {
        $this->artisan('geoip:download')
        ->assertExitCode(0);

        $filePath = storage_path(config('geoip2.folder')).'/'.config('geoip2.filename');
        $this->assertFileExists($filePath);
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