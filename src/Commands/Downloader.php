<?php
namespace danielme85\Geoip2\Commands;
/*
 * The MIT License
 *
 * Copyright 2016 Daniel Mellum.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
use Illuminate\Console\Command;
use GuzzleHttp;

class Downloader extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geoip:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download geoip data from GeoLite2.';

    protected $downloadFile, $downloadTempFile, $downloadUrl, $dbFilePath, $license;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->downloadFile = storage_path('app/GeoLite2-City.mmdb.tar.gz');
        $this->downloadTempFile = storage_path('app/GeoLite2-City.mmdb.tar');
        $this->downloadUrl = config('geoip2.downloadUrl');
        $this->dbFilePath = storage_path(config('geoip2.dbName'));
        $this->license = config('geoip2.license');

        if (!$this->downloadFile or !$this->downloadUrl or !$this->dbFilePath) {
            $this->error('Config settings not found, did you run "php artisan vendor:publish" ?');
            exit();
        }

        try {
            if (file_exists($this->downloadFile)) {
                if ($this->confirm('Found downloaded file, download a new one instead? [y|N]')) {
                    $this->download();
                }
            }
            else {
                $this->download();
            }
            $this->extract();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
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

        $uri = $this->downloadUrl.'&license_key='.$this->license;

        if ($client->request('GET', $uri, ['sink' => $this->downloadFile])) {
            if (is_file($this->downloadFile)) {
                $this->info("File downloaded: $this->downloadFile");
                $success = true;
            }
        }
        return $success;
    }

    /**
     * Extract geoip file
     *
     *
     * @param bool $delete
     * @return bool
     */
    private function extract($delete = true) {
        $success = false;

        if (is_file($this->downloadFile)) {
            $phar = new \PharData($this->downloadFile);
            $phar->decompress();

            if (is_file($this->downloadTempFile)) {
                $phar = new \PharData($this->downloadFile);
                $phar->extractTo(storage_path('app/'));
            }

            if (is_file($this->dbFilePath)) {
                $success = true;
                $this->info("Extracted file to: $this->dbFilePath");
                if ($delete) {
                    if (unlink($this->downloadFile)) {
                        $this->warn("Deleted file: $this->downloadFile");
                    }
                    if (unlink($this->downloadTempFile)) {
                        $this->warn("Deleted file: $this->downloadTempFile");
                    }
                }
            }
        }

        return $success;
    }

}