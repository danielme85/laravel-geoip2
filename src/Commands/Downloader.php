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

    protected $dbFolder, $dbFileName, $downloadUrl, $license, $folderPath, $filePath;

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
     */
    public function handle()
    {
        $this->dbFolder = storage_path(config('geoip2.folder'));
        $this->dbFileName = config('geoip2.filename');
        $this->downloadUrl = config('geoip2.downloadUrl');
        $this->license = config('geoip2.license');
        $this->folderPath = "{$this->dbFolder}/tmp";
        $this->filePath = "{$this->folderPath}/{$this->dbFileName}";


        if (!file_exists($this->filePath.'.tar.gz')) {
            $this->download();
        } else {
            if ($this->confirm('Found downloaded file, download a new one instead?')) {
                $this->download();
            }
        }

        $this->extract();
    }

    /**
     * Download geoip2 db file
     *
     * @return string|null
     */
    private function download()
    {
        $client = new GuzzleHttp\Client();
        $uri = $this->downloadUrl.'&license_key='.$this->license;

        if (!file_exists($this->folderPath)) {
            mkdir($this->folderPath, 0755, true);
        }

        if ($client->request('GET', $uri, ['sink' => $this->filePath.'.tar'])) {
            if (file_exists( $this->filePath.'.tar')) {
                $this->info("File downloaded:  $this->filePath.'.tar'");

                return true;
            }
        }

        return false;
    }

    /**
     * Extract geoip file
     *
     *
     * @return bool
     */
    private function extract()
    {
        $success = false;

        if (file_exists($this->filePath . '.tar.gz') && !file_exists($this->filePath . '.tar')) {
            $phar = new \PharData($this->filePath . '.tar.gz');
            $phar->decompress();
        }

        if (file_exists($this->filePath . '.tar') && !file_exists("{$this->dbFolder}/tmp/extracted")) {
            $phar = new \PharData($this->filePath . '.tar');
            $phar->extractTo("{$this->dbFolder}/tmp/extracted");
        }

        $dirs = array_diff(scandir("{$this->dbFolder}/tmp/extracted"), array('..', '.'));

        $folder = end($dirs);
        $from = "{$this->dbFolder}/tmp/extracted/$folder/$this->dbFileName";
        $to = "{$this->dbFolder}/$this->dbFileName";

        if (file_exists($from)) {
            if (copy($from, $to)) {
                $this->info("File stored: $this->filePath");
                foreach (array_diff(scandir("{$this->dbFolder}/tmp/extracted/$folder"), array('..', '.')) as $file) {
                    if (unlink("{$this->dbFolder}/tmp/extracted/$folder/$file")) {
                        $this->warn("Deleted file: $file");
                    }
                }
                if (rmdir("{$this->dbFolder}/tmp/extracted/$folder")) {
                    $this->warn("Deleted folder: {$this->dbFolder}/tmp/extracted/$folder");
                }
                if (rmdir("{$this->dbFolder}/tmp/extracted")) {
                    $this->warn("Deleted folder: {$this->dbFolder}/tmp/extracted");
                }
                if (unlink("{$this->filePath}.tar")) {
                    $this->warn("Deleted file: {$this->filePath}.tar");
                }
            }
        }

        return $success;
    }
}
