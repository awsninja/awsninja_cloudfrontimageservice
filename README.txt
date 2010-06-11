
CloudfrontService - A PHP Cloudfront Framework - Version 0.0

INTRODUCTION
------------
For details visit the related article on the AWS Ninja blog:
http://wp.me/pWVsZ-4

REQUIREMENTS
------------

PHP 5.0+
MagickWand - http://www.magickwand.org
awsninja_core - Core components for all AWSNinja libraries.


INSTALLATION
------------

  1. Copy the awsninja_cloudfrontimageservice folder to the same location 
     that contains the awsninja_core directory.
  
  2. Move the CloudfrontService-related entries in the config.samp.php to
     the config.php or config.samp.php (depends if you renamed that file)
     and modify them to suit your needs.
  
  3. Run the command in cloudfrontimageservice.sql on your MySQL database
     to create the three needed tables.
  
  4. From the command line, enter the examples subdriectory and  run the 
     configureImageObjects.php script.  You should see the message "Image
     Id 1 created."
  
  5. Then run the cloudfrontImageText.php script.  It should print out a 
     working url of the image on your Cloudfront distribution.  This
     indicates that everything is working correctly.


