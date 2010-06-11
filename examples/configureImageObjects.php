#!/usr/bin/php -q
<?php

define('NINJA_BASEPATH', dirname(__FILE__) . '/../../');
require_once(NINJA_BASEPATH . 'awsninja_cloudfrontimageservice/CloudfrontImageService.php');


$originSystemPath = NINJA_BASEPATH  . 'awsninja_cloudfrontimageservice/examples/flower.jpg'; //SET THIS TO THE CURRENT PATH TO OF THE IMAGE SOURCE YOU WANT TO ADD.
$destination = 'flowers/flower.jpg'; //SET THIS TO THE RELATIVE FILE PATH AND NAME YOU WANT TO USE FOR THE IMAGE

$cfImgSvc = new CloudfrontImageService();
$imgObj = $cfImgSvc->createImageObjectFromFileSystemPath($originSystemPath, $destination);


echo("Image Id {$imgObj->Id} created.\n");










?>