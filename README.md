# laravel-geoip2
Laravel 5+ Service provider and DB downloader for Maxminds PHP API GeoIP2.
https://github.com/maxmind/GeoIP2-php

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://travis-ci.org/danielme85/laravel-geoip2.svg?branch=master)](https://travis-ci.org/danielme85/laravel-geoip2)

<b>Per Jan 2020 you now have to create an account at MaxMind and get a "license key". It is still free of charge per now.</b>

[https://dev.maxmind.com/geoip/geoip2/geolite2/](https://dev.maxmind.com/geoip/geoip2/geolite2/)

### Install
In composer.json
 ```
 "require": {
         "danielme85/laravel-geoip2": "dev-master",
         ....
 }
 ```
 or command: composer require danielme85/laravel-geoip2
 
 Add your geoip licence to your env file:
 ```
GEOIP2_LICENSE=XXXXX
```

#### Laravel 5.x
 Add to your config/app.php under Service Providers
 <br>*(If you use Laravel 5.5+ you could skip this step as Autodiscovery has been enabled for this package.)*
           
 ```
 //Service Providers
 danielme85\Geoip2\Geoip2ServiceProvider::class,
 //Facades
 'Reader'  => danielme85\Geoip2\Facade\Reader::class,
 
 ```
 
#### Lumen 5.x
 Add to your boostrap/app.php file
 ```
 $app->register(danielme85\Geoip2\Geoip2ServiceProvider::class);
 ...
 $app->configure('app'); 
 ...
 class_alias('danielme85\Geoip2\Facade\Reader, 'Reader');
 $app->withFacades();
 ```
 
#### Config
 Publish the config file to your Laravel projects
  ```
php artisan vendor:publish --provider="danielme85\Geoip2\Geoip2ServiceProvider"
  ```
  The following default settings will work right away:
  ```
  return [
      'geoip2' => [
          'downloadUrl' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz', //url db file download
          'tempFile' => 'app/GeoLite2-City.mmdb.gz', //temp download file name
          'dbName' => 'app/GeoLite2-City.mmdb', //Geoip DB filename
          'localhost' => '8.8.8.8' //when running on localhost (or for general testing) you can specify a fake ip address here.
      ]
  ];
  ```
 
### Usage
 You need to download the Maxmind Geoip first, the default config is for the city version (about 30MB download, 50MB extracted).
 ```
 php artisan geoip:download
 ```
 With the DB file downloaded you are ready to get some location data:
 ```
 use danielme85\Geoip2\Facade\Reader;
 ...
 $reader = Reader::connect();
 $result = $reader->city($ip);
 ```
 Usage once you have the Reader:connect object is the same as maxminds documentation
 https://github.com/maxmind/GeoIP2-php.
 
 Example usage, return json location data based on ipv4 address.
 ```php
 <?php
 use danielme85\Geoip2\Facade\Reader;
 ...
 
 function getLocation(Request $request) {
    $reader = Reader::connect();
    /*
    I was experiencing inaccurate results... until I remembered that my web server traffic was routed trough CloudFlare :p
    In that case CloudFlare provides the original client ip in the following header information.
    */   
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    else {
        $ip = $request->ip();
    }
    //the city() function from the GeoIp2 Php API will throw an exception if the ip-address is not found in the DB.
    try {
        $geodata = $reader->city($ip)->jsonSerialize(); //jsonSerialize seems to actually return an associative array.
    }
    catch (\Exception $e) {
        Log::warning($e->getMessage());
        return response()->json("Geo-location not found!", 500);
    }

    return response()->json($geodata);
}
 ```

<small>This product includes GeoLite2 data created by MaxMind, available from
<a href="http://www.maxmind.com" target="_blank">http://www.maxmind.com</a></small>
