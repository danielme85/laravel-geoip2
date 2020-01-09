<?php

return [
    'downloadUrl' => 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&suffix=tar.gz', //url db file download
    'folder' => 'app/geoip2', //storage location folder to store Geoip2 files.
    'filename' => 'GeoLite2-City.mmdb',
    'localhost' => '8.8.8.8', //when running on localhost (or for general testing) you can specify a fake ip address here.
    'license' => env('GEOIP2_LICENSE', '')
];