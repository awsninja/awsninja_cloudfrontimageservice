<?php

/*
 * 
 * CloudfrontImageService
 * 
 */

require_once(NINJA_BASEPATH . 'awsninja_core/db.php');

class ImageDimensions {

	public $Id;
  public $Keyname;
  public $Description;
  public $Width;
  public $Height;
    
  public static function findByKeyName($keyName)
  {
    $db = Db::instance();
    $sql = "SELECT id, keyname, description, width, height FROM tbl_imageDimensions WHERE keyName=:keyName";
    $vals = array(
    	':keyName'=>$keyName
    );
    $res = $db->executeSelectStatement($sql, $vals);
		if(isset($res[0]))
		{
			$r = $res[0];
			
			$imDem = new ImageDimensions();
			$imDem->Id = $r['id'];
			$imDem->Keyname = $r['keyname'];
			$imDem->Description = $r['description'];
			$imDem->Width = $r['width'];
			$imDem->Height = $r['height'];
			return $imDem;								
		}
		else
		{
			return null;
		}
  }
}


?>