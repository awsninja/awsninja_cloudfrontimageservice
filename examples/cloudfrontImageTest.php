#!/usr/bin/php -q
<?php

define('NINJA_BASEPATH', dirname(__FILE__) . '/../../');
require_once(NINJA_BASEPATH . 'awsninja_cloudfrontimageservice/CloudfrontImageService.php');

$destination = 'flowers/flower.jpg'; //SET THIS TO THE RELATIVE FILE PATH OF THE IMAGE YOU WANT TO DISPLAY
$dimensionkey = 'thumbnail';  //MUST BE SET TO A VALID keyname FROM tbl_imageDimensions


//instantiate CloudfrontImageService 
$cfImgSvc = new CloudfrontImageService();

//Get the url by path and dimensionskey
$url = $cfImgSvc->getUrlFromFilePath($destination, $dimensionkey);

echo($url);


?>

