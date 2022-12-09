<?php
/**
 * @param int
 * @return SOYGallery_Gallery
 */
function soygallery_get_gallery_object(int $galleryId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
	if($galleryId <= 0) return new SOYGallery_Gallery();

	try{
		return $dao->getById($galleryId);
	}catch(Exception $e){
		return new SOYGallery_Gallery();
	}
}

/**
 * 文字列の方のgallery_idからSOYGallery_Galleryを取得する
 * @param string
 * @return SOYGallery_Gallery
 */
function soygallery_get_gallery_object_by_gallery_id(string $galleryId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
	
	try{
		return $dao->getByGalleryId($galleryId);
	}catch(Exception $e){
		return new SOYGallery_Gallery();
	}
}


/**
 * @param int, string<column name>, string<DESC or ASC>
 * @return array
 */
function soygallery_get_gallery_objects(int $lim=5, string $sortColumn="create_date", string $sortOrder="DESC"){
	$dao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");	// 毎回DAOを呼び出す
	$dao->setLimit($lim);
	$dao->setOrder($sortColumn . " " . $sortOrder);
	try{
		return $dao->get();
	}catch(Exception $e){
		return array();
	}
}

/**
 * SOYGallery_Galleryオブジェクトにリサイズの設定を入れる
 * @param SOYGallery_Gallery, int, int, int, int, int, int, string
 */
function soygallery_set_gallery_config(SOYGallery_Gallery $gallery, int $resize_width=640, int $resize_height=0, int $thumbnail_width=160, int $thumbnail_height=0, int $count=10, string $uploadDir=""){
	$cnf = array();
	if($resize_width === 0) $resize_width = 640;
	$cnf["resize"]["width"] = $resize_width;
	
	if($resize_height > 0) $cnf["resize"]["height"] = $resize_height;
	
	if($thumbnail_width === 0) $thumbnail_width = 160;
	$cnf["thumbnail"]["width"] = $thumbnail_width;

	if($thumbnail_height > 0) $cnf["thumbnail"]["height"] = $thumbnail_height;
	
	if($count === 0) $count = 10;
	$cnf["count"] = $count;

	if(strlen($uploadDir)){
		$cnf["uploadDir"] = $uploadDir;

		//フォルダがあるか確認
		$uploadDir = str_replace($_SERVER["DOCUMENT_ROOT"], "", $cnf["uploadDir"]);
		$dirs = explode("/", trim($uploadDir, "/"));
		$root = $_SERVER["DOCUMENT_ROOT"];
		foreach($dirs as $dir){
			$root .= "/" . $dir;
			if(!file_exists($root) || !is_dir($root)){
				mkdir($root);
			}
		}
	}
	
	$gallery->setConfigArray($cnf);
}

function soygallery_get_image_object(int $imageId){
	static $dao;
	if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYGallery_ImageDAO");
	if($imageId <= 0) return new SOYGallery_Image();

	try{
		return $dao->getById($imageId);
	}catch(Exception $e){
		return new SOYGallery_Image();
	}
}

/**
 * @param string
 * @return array(width, height)
 */
function soygallery_get_image_file_info(string $path){
	$info = getimagesize($_SERVER["DOCUMENT_ROOT"] . $path);
    return array($info[0],$info[1]);
}

/**
 * @param int, string<column name>, string<DESC or ASC>
 * @return array
 */
function soygallery_get_image_views(int $lim=15, string $sortColumn="create_date", string $sortOrder="DESC"){
	$dao = SOY2DAOFactory::create("SOYGallery_ImageViewDAO");	// 毎回DAOを呼び出す
	$dao->setLimit($lim);
	$dao->setOrder($sortColumn . " " . $sortOrder);
	try{
		return $dao->get();
	}catch(Exception $e){
		return array();
	}
}

/**
 * @param int
 * @return array
 */
function soygallery_get_image_views_by_gId(int $gId){
	try{
		return SOY2DAOFactory::create("SOYGallery_ImageViewDAO")->getByGId($gId);
	}catch(Exception $e){
		return array();
	}
}