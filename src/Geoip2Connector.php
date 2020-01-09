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
        $dbFolder = storage_path(config('geoip2.folder'));
        $dbFileName = config('geoip2.filename');
        $dbFilePath = "{$dbFolder}/$dbFileName";

        if (!is_file($dbFilePath)) {
            abort(500, 'Geoip2 DB file not found!');
        }
        return new \GeoIp2\Database\Reader($dbFilePath);
    }

}