# laravel-geoip2

Laravel service provider and database downloader for [MaxMind's GeoIP2 PHP API](https://github.com/maxmind/GeoIP2-php).

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Tests](https://github.com/danielme85/laravel-geoip2/actions/workflows/tests.yml/badge.svg)](https://github.com/danielme85/laravel-geoip2/actions/workflows/tests.yml)

**Requirements:** PHP 8.3+, Laravel 13.x

> MaxMind requires a free account to download GeoLite2 databases. Sign up at [maxmind.com](https://www.maxmind.com/en/geolite2/signup) to get a license key.

## Install

```bash
composer require danielme85/laravel-geoip2
```

Add your license key to `.env`:
```
GEOIP2_LICENSE=YOUR_LICENSE_KEY
```

Publish the config file:
```bash
php artisan vendor:publish --provider="danielme85\Geoip2\Geoip2ServiceProvider"
```

## Configuration

`config/geoip2.php`:
```php
return [
    'downloadUrl' => 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&suffix=tar.gz',
    'folder'      => 'app/geoip2',       // path inside storage/
    'filename'    => 'GeoLite2-City.mmdb',
    'localhost'   => '8.8.8.8',          // fallback IP when running locally
    'license'     => env('GEOIP2_LICENSE', ''),
];
```

## Usage

Download the database (~30 MB download, ~50 MB extracted):
```bash
php artisan geoip:download
```

Then use the `Reader` facade — the returned object is a [`GeoIp2\Database\Reader`](https://github.com/maxmind/GeoIP2-php), so all MaxMind methods apply directly:

```php
use danielme85\Geoip2\Facade\Reader;

$reader = Reader::connect();
$city   = $reader->city('8.8.8.8');

echo $city->city->name;               // "Mountain View"
echo $city->country->isoCode;         // "US"
echo $city->location->latitude;       // 37.386
echo $city->location->longitude;      // -122.0838
```

### Handling CloudFlare (or other proxies)

```php
$ip     = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $request->ip();

try {
    $geodata = Reader::connect()->city($ip)->jsonSerialize();
} catch (\GeoIp2\Exception\AddressNotFoundException $e) {
    // IP not found in database
}
```

## Re-downloading the database

The downloaded `.tar.gz` archive is kept in `storage/{folder}/tmp/`. Running `geoip:download` again will prompt before fetching a new copy, so you can re-extract from the cached archive or force a fresh download.

---

<small>This product includes GeoLite2 data created by MaxMind, available from <a href="http://www.maxmind.com">maxmind.com</a>.</small>
