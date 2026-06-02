# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a Laravel package (`danielme85/laravel-geoip2`) that wraps [MaxMind's GeoIP2 PHP API](https://github.com/maxmind/GeoIP2-php) with a Laravel service provider, facade, and Artisan command for downloading the database.

**Compatibility**: Laravel 13.x, PHP ^8.3, GeoIP2 ^3.0 (v3 uses readonly properties — `raw` and `record` properties removed, `jsonSerialize` output changed from v2).

## Commands

Install dependencies:
```bash
composer install
```

Run all unit tests:
```bash
vendor/bin/phpunit --testsuite Unit
```

Run integration tests (requires a MaxMind license key — see below):
```bash
vendor/bin/phpunit --testsuite Integration
```

Run a single test:
```bash
vendor/bin/phpunit --filter test_connect_aborts_when_db_file_missing
```

## Test environment

Integration tests need a MaxMind GeoLite2 license key. Set it in `.env.testing`:
```
GEOIP2_LICENSE=YOUR_KEY_HERE
```

Or export it in the shell: `export GEOIP2_LICENSE=YOUR_KEY_HERE`

Integration tests skip themselves gracefully when the license key is absent or the DB file is not present.

## Architecture

Four components in `src/`:

- **`Geoip2ServiceProvider`** — registers the `Reader` IoC binding and `geoip:download` command; merges/publishes `src/config/geoip2.php`.
- **`Geoip2Connector`** — single static `connect()` method that reads `geoip2.folder` + `geoip2.filename` config to locate the `.mmdb` in `storage_path()`, then returns a `\GeoIp2\Database\Reader` instance (aborts 500 if file missing).
- **`Facade\Reader`** — standard Laravel facade resolving the `Reader` binding, proxying to `Geoip2Connector`.
- **`Commands\Downloader`** — `geoip:download` Artisan command. Uses Laravel's HTTP client (`Http::withOptions(['sink' => ...])`) to download the GeoLite2-City `.tar.gz` from MaxMind, decompresses via `PharData`, extracts the `.mmdb` to `storage/{folder}/`, then cleans up temp files. The `.tar.gz` archive is kept in `tmp/` so subsequent runs prompt before re-downloading.

Config keys (`config/geoip2.php`):
- `downloadUrl` — MaxMind download endpoint (GeoLite2-City, tar.gz suffix)
- `folder` — storage subfolder (default `app/geoip2`)
- `filename` — DB filename (default `GeoLite2-City.mmdb`)
- `localhost` — fallback IP for local dev (default `8.8.8.8`)
- `license` — reads from `GEOIP2_LICENSE` env var

The returned `\GeoIp2\Database\Reader` is used directly per [MaxMind's docs](https://github.com/maxmind/GeoIP2-php) (e.g., `->city($ip)`, `->country($ip)`).

## Test layout

```
tests/
  Unit/
    Geoip2ServiceProviderTest.php  — binding registration, config merge
    Geoip2ConnectorTest.php        — abort on missing DB, Reader construction
    DownloaderTest.php             — command registration, HTTP error handling (Http::fake)
  Integration/
    Geoip2IntegrationTest.php      — live download + IP lookup (skipped without license key)
```
