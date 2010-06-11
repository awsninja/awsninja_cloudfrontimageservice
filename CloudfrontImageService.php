<?php
/**
 * CloudfrontImageService
 * 
 * Provide a framework for managing Images on Amazon Cloudfront.  Supports
 * the automatic versioning, redimensioning and URL generation for images
 * for use in web applications.
 * 
 * Requires MagickWand for ImageMagick: http://www.magickwand.org/
 * 
 * @author Jay Muntz
 * 
 * Copyright 2010 Jay Muntz (http://www.awsninja.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * “Software”), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 *
 */
class CloudfrontImageService {

	private $s3;
	private $bucket;
	private $cdnDomain;
	

  public function __construct()
  {
  	require_once(NINJA_BASEPATH . 'awsninja_core/config.php');
		require_once(NINJA_BASEPATH . 'awsninja_core/S3Service.php');  //this is a great S3 library by Donovan Schönknecht
		require_once(NINJA_BASEPATH . 'awsninja_cloudfrontimageservice/classes/Image.php');
		require_once(NINJA_BASEPATH . 'awsninja_cloudfrontimageservice/classes/ImageDimensions.php');
		require_once(NINJA_BASEPATH . 'awsninja_cloudfrontimageservice/classes/ImageDimensionsMap.php');
		$this->bucket = CDN_BUCKET;
		$this->cdnDomain = CDN_DOMAIN_NAME;
		$this->imagesRoot = IMAGES_ROOT;
		$this->s3 = new S3Service(); 
		$this->s3->setAuth(AWS_ACCESS_KEY, AWS_SECRET_KEY);
  }

  
  /**
   * Returns the URL to the image requested
   * 
   * Example Usage: echo($imgSvc->getUrlFromFilePath('wwwimages/logo.jpg', 'originial'));
   * 
   * @param string $filePath  The relative path to the image on the filesystem.
   * @param string $dimensionsKeyname  A string that identifies the dimensions of the resulting image (must be in tbl_imageDimensions.keyname)
   * @return string  A URL.
   */
 	public function getUrlFromFilePath($filePath, $dimensionsKeyname)
 	{
    $img = Image::findByFilePath($filePath);
    if (!isset($img))
		{
			throw new Exception("Could not find Image object by path $filePath");
		}
  	return $this->getURL($img, $dimensionsKeyname);
 	}

 	
 	/**
 	 * Returns the URL to the image requested
 	 * 
 	 * @param ing $imageId An image id from tbl_images
 	 * @param string $dimensionsKeyname A string that identifies the dimensions of the resulting image (must be in tbl_imageDimensions.keyname)
 	 * @return string  A URL.
 	 */
 	public function getUrlFromImageId($imageId, $dimensionsKeyname)
 	{
   	$img = Image::findById($imageId);
  	if (!isset($img))
		{
			throw new Exception("Could not find Image object by id $imageId");
		}
	 	return $this->getURL($img, $dimensionsKeyname);
 	}

 	
	/**
	 * Returns the URL to the image requested
	 * 
	 * @param $image and Image object
	 * @param $dimensionsKeyname A string that identifies the dimensions of the resulting image (must be in tbl_imageDimensions.keyname)
	 * @return string  A URL.
	 */
	public function getURL(Image $image, $dimensionsKeyname)
	{
		$idim = $this->getDimensionByKeyname($dimensionsKeyname);
		$imageDimensionMap = ImageDimensionsMap::findByImageIdAndDimensionsId($image->Id, $idim->Id);
		if (!isset($imageDimensionMap))
		{
			$imageDimensionMap = $this->createAndUploadImageDimensionMap($image, $dimensionsKeyname);
		}
		if ($imageDimensionMap->Version < $image->Version)
		{
			
			$imageDimensionMap = $this->createAndUploadImageDimensionMap($image, $dimensionsKeyname);
		}
		$pth = $this->getPath($image, $imageDimensionMap, $idim);
		return "http://{$this->cdnDomain}/{$pth}";
	}



	/**
	 * This is a utility function for adding images to your system.  This can be used in 
	 * a console application to pick up an image located at $fsPath and copy it to
	 * your disk-based data store and create a record in the Images table.
	 * 
	 * @param string $fsPath The full path to the image you want to add to the system
	 * @param string $filePath The relative path and file name that you want to use for the resulting Image 
	 * @return string  An Image object.
	 */
	public function createImageObjectFromFileSystemPath($fsPath, $filePath)
	{
		if (!file_exists($fsPath))
		{
			throw new Exception("File $fsPath does not exist");
		}
		$magick_wand=NewMagickWand();
		MagickReadImage($magick_wand,$fsPath);
		
		//check the image type
		$mimeType = MagickGetImageMimeType($magick_wand);
		$typeStr = '';
		switch($mimeType)
		{
			case 'image/png':
				$typeStr = 'PNG';
			break;
			case 'image/jpg':
			case 'image/jpeg':
				$typeStr = 'JPG';
			break;
			case 'image/gif':
				$typeStr = 'GIF';
			break;
			default:
				throw new Exception("Unknown image mime-type $mimeType");			
			break;
		}
		
		//Ensure that $filePath is not the path of another Image already in the system.  If
		//it is a duplicate, append an interger to the file name to make it unique.
		$unique = false;  //initialze the unique flag to false
		$parts = pathinfo($filePath); //get the path parts
		$baseName = "{$parts['dirname']}/{$parts['filename']}";  //the full path minus file extension
		$ext = $parts['extension']; //the extention
		$ct = 0; //the integer we will append to the filename, if necessary
		$testFile = "{$baseName}.{$ext}";
		while(!$unique)
		{
			$existImg = Image::findByFilePath($testFile); //check the database to see if this path already exists
			if (!isset($existImg))
			{
				$unique = true;  //it doesn't exist.  $unique==true will break the while
			}
			else
			{
				$ct++;  //it does exist - increment the counter, change the $testFile, then loop to test the file name
				$testFile =  "{$baseName}{$ct}.{$ext}";
			}
		}
		$finalFilePath = $testFile;
		$this->ensureImagePath($finalFilePath);
		$fullImagePath = $this->imagesRoot . $finalFilePath;
		copy($fsPath, $fullImagePath);  //copy the file to it's official location
		$img = new Image(); //create new Image Object
		$img->FilePath = $finalFilePath;
		$img->ImageType = $typeStr;
		$img->Version = 1;
		$img->save(); //commit to database
		return $img;
	}
	
	//Recursively makes sure the directories that make up $filePath exist
	private function ensureImagePath($filePath) //$filePath is the path relative to the base image path
	{
		$fullPath = dirname(IMAGES_ROOT . $filePath);
		if (!file_exists($fullPath)) //only enter the crawl if the final result doesn't exist
		{
			$parts = explode('/', $fullPath);
			$subPath = '/';
			for($i=1;$i<count($parts); $i++)
			{
				$subPath .= $parts[$i] . '/';
				if (!file_exists($subPath))
				{
					mkdir( $subPath);
				}
			}
		}
	}
	
	
  //when getUrl() is called, it checks to see if we have the given ImageDimensionMap, which
	//also tells us if the file is uploaded to CloudFront.  If not, it calls this method
	//to handle the upload before it returns the URL to the caller.
	private function createAndUploadImageDimensionMap(Image $image, $dimensionsKeyname)
	{
		$imd = $this->getDimensionByKeyname($dimensionsKeyname);
		$srcPth = $this->imagesRoot . $image->FilePath;
		if(file_exists($srcPth))
		{
			$tempPath =  tempnam(sys_get_temp_dir(), 'img_');
			if ($dimensionsKeyname == 'original') //dimenisons "original" means the original size is requsted 
			{
				$s3Path = $srcPth;
				$pts = getimagesize($s3Path);
				$width = $pts[0];
				$height = $pts[1];
				$newDims = array(
					'x'=>$width,
					'y'=>$height
				);
				$s3Path = $srcPth;
			}	
			else
			{
				//need to redimension this image, using MagickWand
				$magick_wand=NewMagickWand();
				MagickReadImage($magick_wand,$srcPth);
				$width = MagickGetImageWidth($magick_wand);
				$height = MagickGetImageHeight($magick_wand);
				$newDims = $this->getNewDimensionsPreservingAspectRatio($width, $height, $imd->Width, $imd->Height);
				MagickScaleImage($magick_wand, $newDims['x'],  $newDims['y']);
				MagickWriteImage($magick_wand, $tempPath);
				$s3Path = $tempPath;
			}	
			$headers = array();  //This holds the http headers for our S3 object.
			switch($image->ImageType)
			{
				case 'GIF':
					$headers['Content-Type'] = 'image/gif';
				break;
				case 'JPG':
					$headers['Content-Type'] = 'image/jpeg';
				break;
				case 'PNG':	
					$headers['Content-Type'] = 'image/png';
				break;
				default:
					throw new Exception("Unrecognized type {$image->getType()}");
				break;
			}

			//A "Far Future" expiration - maximizing the chance that the user's web browser will use
			//a cached version rather than requesting the file from Cloudfront.
			//Also set to public (as recommended by Google Speed Tracer), so that caching will work when
			//using SSL as well.  Even though Cloudfont doesn't support ssl today, someday it will
			//and we will be prepared! 
 			$headers['Cache-Control'] = 'public, max-age=315360000'; //10 years

			$imDimMap = new ImageDimensionsMap();
			$imDimMap->ImageId = $image->Id;
			$imDimMap->ImageDimensionsId = $imd->Id;
			$imDimMap->Width = $newDims['x'];
			$imDimMap->Height = $newDims['y'];
			$imDimMap->Version = $image->Version;
			$imDimMap->save();


			//upload the new file to S3
			try
			{
				$acl = S3Service::ACL_PUBLIC_READ;
				if (!file_exists($tempPath))
				{
					throw new Exception("$tempPath dosn't exist");
				}

				$res = $this->s3->putObject(S3Service::inputFile($s3Path, true), $this->bucket,  $this->getPath($image, $imDimMap, $imd),$acl, array(), $headers);
	
				if ($res)
				{
					unlink($tempPath);		
				}
				else
				{
					//something's wrong.  Fail silently and just leave the old version if it exists.
					//Don't throw an exception or raise a failure, because there's a user on the other side
					//of this request waiting to see this image. 
					$imDimMap->Version = $imDimMap->Version-1;
					$imDimMap->save();				
				}
			}
			catch (Exception $e)
			{
				//something's wrong.  Fail silently and just leave the old version if it exist.
				//Don't throw an exception or raise a failure, because there's a user on the other side
				//of this request waiting to see this image. 
				$imDimMap->Version = $imDimMap->Version-1;
				$imDimMap->save();
			}
		}
		return $imDimMap;
	}
	

	//Get the new dimensions that will fit in the ImageDimensions "box" preserving aspect ratio.
	private function getNewDimensionsPreservingAspectRatio($oldX, $oldY, $targetX, $targetY)
	{
		$oldAspectRatio = $oldX/$oldY;
		$targAspectRatio = $targetX/$targetY;
		$res = array();
		if ($targAspectRatio < $oldAspectRatio)
		{
			//bound to width(X)
			$res['x'] = $targetX;
			$res['y'] = ceil($targetX*$oldY/$oldX);
		
		}
		else
		{
			//bound to height(Y)
			$res['y'] = $targetY;
			$res['x'] = ceil($targetY*$oldX/$oldY);
		}
		return $res;		
	}

	//Takes all three objects and returns the actual path which look something like this:
	//		previews/0000/0079/00000838/1_1_book_thumb_130x168.png
	private function getPath(Image $image, ImageDimensionsMap $imgDimMap, ImageDimensions $imageDimensions)
	{
		$parts = pathinfo($image->FilePath);
		return "{$parts['dirname']}/{$parts['filename']}{$this->getVersionDimsSlug($imgDimMap, $imageDimensions)}.{$parts['extension']}";
	}
	
	//Returns the part of the url made up of the version, dimension key and the actual width and
	//height of hte image.  Something like this:
	//		_1_book_thumb_130x168
	private function getVersionDimsSlug(ImageDimensionsMap $imageDimensionMap, ImageDimensions $imageDimensions)
	{
		return "_{$imageDimensionMap->Version}_{$imageDimensions->Keyname}_{$imageDimensionMap->Width}x{$imageDimensionMap->Height}";
	}
	
	//get the ImageDimension object by it's keyname
	private function getDimensionByKeyname($dimensionsKeyname)
	{
    $idim = ImageDimensions::findByKeyname($dimensionsKeyname);
  	if (!isset($idim))
		{
			throw new Exception("Could not find ImageDimensions object by keyname $dimensionsKeyname");
		}
    return $idim;
	}
  
}

?>