<?php

/*
 * 
 * AWSNinja - CloudfrontImageService
 * Set the following global variables and rename this vile to config.php
 * 
 */

define('CDN_BUCKET', 'mycdnbucket');  ///The S3 bucket that you use for your Cloudfront Distuibution
define('CDN_DOMAIN_NAME', 'cdn.mydomain.com');  //The domain name you use for your Cloudfront Distribution
define('IMAGES_ROOT', '/path/to/image/storage/');  //The root path where you will store your Images on the filesystem


/*
 * 
 * AWSNinja - Core
 * 
 * The following constants are used by the and other packagescore package. 
 * 
 */

define('AWS_ACCESS_KEY', 'YOUR_AWS_ACCESS_KEY');
define('AWS_SECRET_KEY', 'YOUR_AWS_SECRET_KEY');

//These are used by the db.php database helper.
define('DB_CONNECTION_STRING', 'mysql:host=localhost;dbname=yourdb');

define('DB_USERNAME', 'db_user');
define('DB_PASSWORD', 'your_password');


?>