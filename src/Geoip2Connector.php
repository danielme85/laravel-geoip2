<?php
/**
 * Created by PhpStorm.
 * User: dmellum
 * Date: 11/21/16
 * Time: 3:28 PM
 *  ___       _               _           _               ___
 * |_ _|_ __ | |_ ___ _ __ __| | ___  ___(_) __ _ _ __   |_ _|_ __   ___
 *  | || '_ \| __/ _ \ '__/ _` |/ _ \/ __| |/ _` | '_ \   | || '_ \ / __|
 *  | || | | | ||  __/ | | (_| |  __/\__ \ | (_| | | | |  | || | | | (__
 * |___|_| |_|\__\___|_|  \__,_|\___||___/_|\__, |_| |_| |___|_| |_|\___|
 *                                          |___/
 */

namespace danielme85\Geoip2;

class Geoip2Connector
{
    public static function connect()
    {
        if (!config('geoip2.dbName')) {
            abort(500, 'Config settings not found, did you run "php artisan vendor:publish" ?');
        }
        if (!is_file(storage_path(config('geoip2.dbName')))) {
            abort(500, 'Geoip2 DB file not found!');
        }
        return new \GeoIp2\Database\Reader(storage_path(config('geoip2.dbName')));
    }

}