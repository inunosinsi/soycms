<?php
SOY2::import("util.SOYAppUtil");
function soyshop_soygallery_display($html, $page){

	//ギャラリIDを取得
	preg_match('/(<[^>]*[^\/]app:gallery="(.+?)"[^>]*>)/', $html, $tmp);
	$instructTag = (isset($tmp[1]) && strlen($tmp[1])) ? $tmp[1] : null;
	$galleryId = (isset($tmp[2]) && strlen($tmp[2])) ? $tmp[2] : null;

	//指示タグを削除
	$html = str_replace($instructTag, "", $html);

	$obj = $page->create("soygallery_display", "HTMLTemplatePage", array(
		"arguments" => array("soygallery_display", $html)
	));

	//SOY Galleryのconfigから
	$old = SOYAppUtil::switchAppMode("gallery");

	$gallery = getGalleryBySOYGallery($galleryId);
	$config = $gallery->getConfigArray();

	if(isset($config["uploadDir"]) && strlen($config["uploadDir"])){
		$dir = $config["uploadDir"];
	}else{
		$dir = soy2_realpath($_SERVER["DOCUMENT_ROOT"])."GalleryImage/" . $gallery->getGalleryId() . "/";
	}
	define("SOY_GALLERY_IMAGE_UPLOAD_DIR", $dir);
	define("SOY_GALLERY_IMAGE_ACCESS_PATH", "/" . trim(str_replace($_SERVER["DOCUMENT_ROOT"], "", SOY_GALLERY_IMAGE_UPLOAD_DIR) , "/") . "/");

	$limit = (isset($config["count"])) ? (int)$config["count"] : 15;
	$images = getImagesBySOYGallery($galleryId, $limit);

	SOYAppUtil::resetAppMode($old);


	$obj->addLabel("gallery_name", array(
		"soy2prefix" => "block",
		"text" => $gallery->getName()
	));

	$obj->addLabel("gallery_memo", array(
		"soy2prefix" => "block",
		"text" => $gallery->getMemo()
	));

	$obj->createAdd("image_list", "SOYShop_GalleryComponent", array(
		"soy2prefix" => "block",
		"list" => $images
	));

	$obj->display();
}

function getGalleryBySOYGallery($galleryId){
	$galleryDao = SOY2DAOFactory::create("SOYGallery_GalleryDAO");
	try{
		$gallery = $galleryDao->getByGalleryId($galleryId);
	}catch(Exception $e){
		$gallery = new SOYGallery_Gallery();
	}
	return $gallery;
}
function getImagesBySOYGallery($galleryId, $limit){
	$imageDao = SOY2DAOFactory::create("SOYGallery_ImageViewDAO");

	$imageDao->setLimit($limit);
	try{
		$images = $imageDao->getByGalleryIdAndIsPublic($galleryId);
	}catch(Exception $e){
		$images = array();
	}

	return $images;
}

class SOYShop_GalleryComponent extends HTMLList{

	protected function populateItem($entity, $index){

		$this->addLabel("id", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getId()
		));

		$this->addLabel("filename", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getFilename()
		));

		$this->addLabel("thumbnail_filename", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => "t_".$entity->getFilename()
		));

		$this->addLink("image_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => SOY_GALLERY_IMAGE_ACCESS_PATH . $entity->getFilename()
		));

		$attributes = $entity->getAttributeArray();
		$this->addImage("image", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"src" => SOY_GALLERY_IMAGE_ACCESS_PATH . $entity->getFilename(),
			"attr:alt" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));

		$this->addLabel("image_path", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => SOY_GALLERY_IMAGE_ACCESS_PATH . "t_" . $entity->getFilename()
		));

		$this->addLink("thumbnail_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => SOY_GALLERY_IMAGE_ACCESS_PATH . "t_" . $entity->getFilename()
		));

		$this->addImage("thumbnail", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"src" => SOY_GALLERY_IMAGE_ACCESS_PATH . "t_" . $entity->getFilename(),
			"attr:alt" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));

		$this->addLabel("thumbnail_path", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => SOY_GALLERY_IMAGE_ACCESS_PATH . "t_" . $entity->getFilename()
		));

		//サイズを調べて、縦横どちらが長いかを調べる。正方形の場合はwidth
		$imageInfo = getimagesize(SOY_GALLERY_IMAGE_UPLOAD_DIR.$entity->getFilename());
		$imageType = ($imageInfo[1] > $imageInfo[0]) ? "height" : "width";

		$this->addLabel("image_type",array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $imageType
		));

		$this->addLabel("image_type_wamei",array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => ($imageType==="width") ? "yoko" : "tate"
		));

		$this->addLabel("url",array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getUrl()
		));

		$this->addLink("url_link",array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => $entity->getUrl()
		));

		$this->addLabel("memo", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => nl2br($entity->getMemo())
		));

		$this->addLabel("sort", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $index + 1
		));

		if(!class_exists("DateLabel")) include_once(CMS_COMMON . "site_include/DateLabel.class.php");
		$this->createAdd("create_date", "DateLabel", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getCreateDate()
		));

		$this->createAdd("update_date", "DateLabel", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $entity->getUpdateDate()
		));
	}
}
