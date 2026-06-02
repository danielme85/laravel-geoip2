<?php

namespace danielme85\Geoip2;

use GeoIp2\Database\Reader;

class Geoip2Connector
{
    public static function connect(): Reader
    {
        $dbPath = storage_path(config('geoip2.folder') . '/' . config('geoip2.filename'));

        if (!is_file($dbPath)) {
            abort(500, 'Geoip2 DB file not found at: ' . $dbPath);
        }

        return new Reader($dbPath);
    }
}
