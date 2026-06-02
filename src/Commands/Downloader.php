<?php

namespace danielme85\Geoip2\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class Downloader extends Command
{
    protected $signature = 'geoip:download';

    protected $description = 'Download the GeoLite2 database from MaxMind.';

    private string $dbFolder;
    private string $dbFileName;
    private string $downloadUrl;
    private string $license;
    private string $tmpFolder;
    private string $tmpFilePath;

    public function handle(): int
    {
        $this->dbFolder   = storage_path(config('geoip2.folder'));
        $this->dbFileName = config('geoip2.filename');
        $this->downloadUrl = config('geoip2.downloadUrl');
        $this->license    = config('geoip2.license');
        $this->tmpFolder  = "{$this->dbFolder}/tmp";
        $this->tmpFilePath = "{$this->tmpFolder}/{$this->dbFileName}";

        $archivePath = "{$this->tmpFilePath}.tar.gz";

        if (file_exists($archivePath)) {
            if (!$this->confirm('Found a previously downloaded archive. Download a fresh copy instead?')) {
                return $this->extract() ? self::SUCCESS : self::FAILURE;
            }
        }

        if (!$this->download()) {
            return self::FAILURE;
        }

        return $this->extract() ? self::SUCCESS : self::FAILURE;
    }

    private function download(): bool
    {
        $uri = $this->downloadUrl . '&license_key=' . $this->license;

        if (!file_exists($this->tmpFolder)) {
            mkdir($this->tmpFolder, 0755, true);
        }

        $archivePath = "{$this->tmpFilePath}.tar.gz";

        $response = Http::withOptions(['sink' => $archivePath])->get($uri);

        if (!$response->successful() || !file_exists($archivePath)) {
            $this->error("Download failed (HTTP {$response->status()}).");
            return false;
        }

        $this->info("Downloaded archive to: {$archivePath}");
        return true;
    }

    private function extract(): bool
    {
        $archivePath = "{$this->tmpFilePath}.tar.gz";
        $tarPath     = "{$this->tmpFilePath}.tar";
        $extractDir  = "{$this->tmpFolder}/extracted";
        $destination = "{$this->dbFolder}/{$this->dbFileName}";

        if (!file_exists($archivePath)) {
            $this->error("Archive not found: {$archivePath}");
            return false;
        }

        if (!file_exists($tarPath)) {
            $phar = new \PharData($archivePath);
            $phar->decompress();
            $this->line("Decompressed to: {$tarPath}");
        }

        if (!file_exists($extractDir)) {
            $phar = new \PharData($tarPath);
            $phar->extractTo($extractDir);
            $this->line("Extracted to: {$extractDir}");
        }

        $entries = array_values(array_diff(scandir($extractDir), ['.', '..']));
        if (empty($entries)) {
            $this->error('Extracted archive is empty.');
            return false;
        }

        $sourceFile = "{$extractDir}/{$entries[0]}/{$this->dbFileName}";

        if (!file_exists($sourceFile)) {
            $this->error("Expected DB file not found: {$sourceFile}");
            return false;
        }

        if (!file_exists($this->dbFolder)) {
            mkdir($this->dbFolder, 0755, true);
        }

        if (!copy($sourceFile, $destination)) {
            $this->error("Failed to copy DB to: {$destination}");
            return false;
        }

        $this->info("Database installed: {$destination}");
        $this->cleanup($extractDir, $entries[0], $tarPath);

        return true;
    }

    private function cleanup(string $extractDir, string $innerFolder, string $tarPath): void
    {
        $innerDir = "{$extractDir}/{$innerFolder}";

        foreach (array_diff(scandir($innerDir), ['.', '..']) as $file) {
            unlink("{$innerDir}/{$file}");
        }

        rmdir($innerDir);
        rmdir($extractDir);

        if (file_exists($tarPath)) {
            unlink($tarPath);
        }

        $this->line('Cleaned up temporary files.');
    }
}
