<?php

return [
    'downloadUrl' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz', //url db file download
    'tempFile' => 'app/GeoLite2-City.mmdb.gz', //temp download file name
    'dbName' => 'app/GeoLite2-City.mmdb', //Geoip DB filename
    'localhost' => '8.8.8.8' //when running on localhost (or for general testing) you can specify a fake ip address here.
];