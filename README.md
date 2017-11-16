# laravel-geoip2
Service provider and DB downloader, Laravel 5.3 for Maxminds PHP API GeoIP2.
https://github.com/maxmind/GeoIP2-php

###Install
Add to composer.json
 ```
 "require": {
         "danielme85/laravel-geoip2": "dev-master",
         ....
 }
 ```
 or command: composer require danielme85/laravel-geoip2

####Laravel 5.x
 Add to your config/app.php under Service Providers
 <br>*(If you use Laravel 5.5+ you could skip this step as Autodiscovery has been enabled for this package.)*
           
 ```
 //Service Providers
 danielme85\ForceUTF8\Geoip2ServiceProvider::class,
 //Facades
 'Reader'  => danielme85\Geoip2\Facade\Reader::class,
 
 ```
 
####Lumen 5.x
 Add to your boostrap/app.php file
 ```
 $app->register(danielme85\ForceUTF8\Geoip2ServiceProvider::class);
 ...
 $app->configure('app'); 
 ...
 class_alias('danielme85\Geoip2\Facade\Reader, 'Reader');
 $app->withFacades();
 ```
 
 ####Config
 Publish the config file to your Laravel projects
  ```
  php artisan vendor:publish
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
 
 ###Usage
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
https://github.com/maxmind/GeoIP2-php

<small>This product includes GeoLite2 data created by MaxMind, available from
<a href="http://www.maxmind.com" target="_blank">http://www.maxmind.com</a></small>
