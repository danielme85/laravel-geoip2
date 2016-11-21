<?php

return [
    'geoip2' => [
        'downloadUrl' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz', //url db file download
        'tempFile' => 'app/GeoLite2-City.mmdb.gz', //temp download file name
        'dbName' => 'app/GeoLite2-City.mmdb', //Geoip DB filename
    ]
];