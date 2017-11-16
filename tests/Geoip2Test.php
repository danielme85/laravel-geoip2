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
     * A basic test example. <- just a test for the test to test if the test is testing.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    /**
     * Test the geopip2 maxmind database download.
     *
     * @return void
     */
    public function testDatabaseDownload()
    {
        $this->downloadUrl = config('geoip2.downloadUrl');
        $this->downloadFile = storage_path(config('geoip2.tempFile'));
        $this->dbFilePath = storage_path(config('geoip2.dbName'));

        //if present dont download again
        if (!file_exists($this->dbFilePath)) {
            if (!file_exists($this->downloadFile)) {
                $this->download();
            }
            if (file_exists($this->downloadFile) and !file_exists($this->dbFilePath)) {
                $this->extract();
            }
        }

        $this->assertFileExists($this->dbFilePath);
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


    /**
     * Download geoip file
     *
     * @return bool
     */
    private function download()
    {
        $success = false;
        $client = new GuzzleHttp\Client();
        if ($client->request('GET', $this->downloadUrl, ['sink' => $this->downloadFile])) {
            if (is_file($this->downloadFile)) {
                $success = true;
            }
        }
        return $success;
    }

    /**
     * Extract geoip file
     *
     * https://gist.github.com/james2doyle/079292f8c498a427852b7f312fa94532
     *
     * @param bool $delete
     * @return bool
     */
    private function extract($delete = true)
    {
        $success = false;

        // Raising this value may increase performance
        $buffer_size = 4096; // read 4kb at a time
        // Open our files (in binary mode)
        $file = gzopen($this->downloadFile, 'rb');
        $out_file = fopen($this->dbFilePath, 'wb');
        // Keep repeating until the end of the input file
        while (!gzeof($file)) {
            // Read buffer-size bytes
            // Both fwrite and gzread and binary-safe
            fwrite($out_file, gzread($file, $buffer_size));
        }
        // Files are done, close files
        fclose($out_file);
        gzclose($file);

        if (is_file($this->dbFilePath)) {
            $success = true;
            if ($delete) {
                unlink($this->downloadFile);
            }
        }
        return $success;
    }

}