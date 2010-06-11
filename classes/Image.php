<?php

/*
 * 
 * CloudfrontImageService
 * 
 */

require_once(NINJA_BASEPATH . 'awsninja_core/db.php');

	class Image {
    public $Id;
    public $FilePath;
    public $Version;    
    public $ImageType;


    public function save() 
    {
    	if (isset($this->Id))
    	{
    		self::update($this);
    	}
    	else
    	{
    		$id = self::insert($this);
    		$this->Id = $id;
    	}
   		return true;
    }
    
    
    public static function insert(Image $img)
    {
    	$db = Db::instance();
    	$sql = "INSERT INTO tbl_image (filePath, imageType, version, filePath_hashIndex) VALUES (:filePath, :imageType, :version, crc32(:filePath))";
    	$vals = array(
    		':filePath'=>$img->FilePath,
    		':imageType'=>$img->ImageType,
    		':version'=>$img->Version
    	);
    	$id = $db->executeInsertStatement($sql, $vals);
    	return $id;
    }
    
    public static function findById($imageId)
    {
   		$db = Db::instance();
    	$sql = "SELECT id, filePath, imageType, version FROM tbl_image WHERE id=:id";
    	$vals = array(
    		':id'=>$imageId
    	);
    	$res = $db->executeSelectStatement($sql, $vals);
			if(isset($res[0]))
			{
				$r = $res[0];
				$img = new Image();
				$img->Id = $r['id'];
				$img->FilePath = $r['filePath'];
				$img->Version = $r['version'];
				$img->ImageType = $r['imageType'];
				return $img;								
			}
			else
			{
				return null;
			}
    }
    
    public static function findByFilePath($filePath)
    {
    	$db = Db::instance();
    	$sql = "SELECT id, filePath, imageType, version FROM tbl_image WHERE filePath=:filePath and filePath_hashIndex=crc32(:filePath)";
    	$vals = array(
    		':filePath'=>$filePath
    	);
    	$res = $db->executeSelectStatement($sql, $vals);
			if(isset($res[0]))
			{
				$r = $res[0];
				$img = new Image();
				$img->Id = $r['id'];
				$img->FilePath = $r['filePath'];
				$img->Version = $r['version'];
				$img->ImageType = $r['imageType'];
				return $img;
			}
			else
			{
				return null;
			}
    }
  }


?>