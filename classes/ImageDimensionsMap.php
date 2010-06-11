<?php

/*
 * 
 * CloudfrontImageService
 * 
 */

require_once(NINJA_BASEPATH . 'awsninja_core/db.php');

class ImageDimensionsMap {

	public $Id;
  public $ImageId;
  public $ImageDimensionsId;    
  public $Width;
  public $Height;
	public $Version;

  public function save() 
  {
    $id = self::replaceInto($this);
   	$this->Id = $id;
    return true;
  }
    
  public static function replaceInto(ImageDimensionsMap $idm)
  {
    $db = Db::instance();
    $sql = "REPLACE INTO tbl_imageDimensionsMap (imageId, imageDimensionsId, width, height, version) VALUES (:imageId, :imageDimensionsId, :width, :height, :version)";
    $vals = array(
    	':imageId'=>$idm->ImageId,
    	':imageDimensionsId'=>$idm->ImageDimensionsId,
    	':width'=>$idm->Width,
    	':height'=>$idm->Height,
    	':version'=>$idm->Version
    );
    $id = $db->executeInsertStatement($sql, $vals);
    return $id;
  }

  public static function findByImageIdAndDimensionsId($imageId, $imageDimensionsId)
  {
    $db = Db::instance();
    $sql = "SELECT id, imageId, imageDimensionsId, width, height, version FROM tbl_imageDimensionsMap WHERE imageId=:imageId AND imageDimensionsId=:imageDimensionsId";
    $vals = array(
    	':imageId'=>$imageId,
    	':imageDimensionsId'=>$imageDimensionsId
    );
    $res = $db->executeSelectStatement($sql, $vals);
    if(isset($res[0]))
		{
			$r = $res[0];
			$idm = new ImageDimensionsMap();
			$idm->Id = $r['id'];
			$idm->ImageId = $r['imageId'];
			$idm->ImageDimensionsId = $r['imageDimensionsId'];
			$idm->Width = $r['width'];
			$idm->Height = $r['height'];
			$idm->Version = $r['version'];
			return $idm;								
		}
		else
		{
			return null;
		}
   }
}


?>