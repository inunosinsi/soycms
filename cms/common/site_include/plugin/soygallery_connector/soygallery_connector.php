<?php
$obj = CMSPlugin::loadPluginConfig(SOYGalleryConnectorPlugin::PLUGIN_ID);
if(is_null($obj)){
	$obj = new SOYGalleryConnectorPlugin();
}
CMSPlugin::addPlugin(SOYGalleryConnectorPlugin::PLUGIN_ID,array($obj,"init"));

class SOYGalleryConnectorPlugin{

	const PLUGIN_ID = "soygallery_connector";

	function getId(){
		return self::PLUGIN_ID;
	}
	
	private $siteId;

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"SOY Gallery連携プラグイン",
			"description"=>"SOY Galleryと連携するために使用します。<br /><br />どのページでもギャラリーを呼び出せるようになります",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.0"
		));
		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			CMSPlugin::setEvent('onPageOutput',$this->getId(),array($this,"onPageOutput"));
		}

	}
	
	function onPageOutput($obj){
		
		$obj->createAdd("soygallery_plugin","SOYGalleryComponent",array(
			"soy2prefix" => "app",
		));
	}

	/**
	 * 設定画面
	 */
	function config_page($message){
		
		ob_start();
		include_once(dirname(__FILE__) . "/config.php");
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}

class SOYGalleryComponent extends SOYBodyComponentBase{
	
	function execute(){
		$galleryId = $this->getAttribute("app:galleryId");
		
		//タグが正しく書かれていない場合は処理をやめる
		if(is_null($galleryId))return;
		
		if(!defined("SOY_GALLERY_IMAGE_ACCESS_PATH"))define("SOY_GALLERY_IMAGE_ACCESS_PATH", "/GalleryImage/");
		if(!defined("SOY_GALLERY_IMAGE_UPLOAD_DIR"))define("SOY_GALLERY_IMAGE_UPLOAD_DIR", soy2_realpath($_SERVER["DOCUMENT_ROOT"])."GalleryImage/");
		
		$count = (!is_null($this->getAttribute("app:count"))) ? (int)$this->getAttribute("app:count") : 5;
		
		include_once(dirname(__FILE__)."/class/common.php");
		$old = SOYGalleryCommon::setConfig();
		
		$images = $this->getImages($galleryId,$count);
		
		$this->createAdd("image_list","SOYGalleryPluginImageListComponent",array(
			"soy2prefix" => "cms",
			"list" => $images
		));
		
		SOYGalleryCommon::resetConfig($old);
		
		parent::execute();
	}
	
	function getImages($galleryId,$count){
		$dao = SOY2DAOFactory::create("SOYGallery_ImageViewDAO");
		
		$dao->setLimit($count);
		try{
			$images = $dao->getByGalleryIdAndIsPublic($galleryId);
		}catch(Exception $e){
			$images = array();
		}
		
		return $images;
	}
}

class SOYGalleryPluginImageListComponent extends HTMLList{
	
	protected function populateItem($entity){
		$prefix = "cms";
		
		$this->addLabel("id",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getId()
		));
		
		$this->addLabel("filename",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getFilename()
		));
		
		$this->addLabel("thumbnail_filename",array(
			"soy2prefix" => $prefix,
			"text" => "t_".$entity->getFilename()
		));
		
		$path = SOY_GALLERY_IMAGE_ACCESS_PATH . $entity->getGalleryId() . "/";
		
		$this->addLink("image_link",array(
			"soy2prefix" => $prefix,
			"link" => $path . $entity->getFilename()
		));
		
		$attributes = $entity->getAttributeArray();
		$this->addImage("image",array(
			"soy2prefix" => $prefix,
			"src" => SOY_GALLERY_IMAGE_ACCESS_PATH.$entity->getGalleryId()."/".$entity->getFilename(),
			"attr:alt" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));
		
		$this->addLabel("image_path",array(
			"soy2prefix" => $prefix,
			"text" => $path . "t_" . $entity->getFilename()
		));
		
		$this->addLink("thumbnail_link",array(
			"soy2prefix" => $prefix,
			"link" => $path . "t_" . $entity->getFilename()
		));
		
		$this->addImage("thumbnail",array(
			"soy2prefix" => $prefix,
			"src" => $path . "t_" . $entity->getFilename(),
			"attr:alt" => (isset($attributes["alt"])) ? $attributes["alt"] : ""
		));
		
		$this->addLabel("thumbnail_path",array(
			"soy2prefix" => $prefix,
			"text" => $path . "t_" . $entity->getFilename()
		));
				
		//サイズを調べて、縦横どちらが長いかを調べる。正方形の場合はwidth
		$imageDir = SOY_GALLERY_IMAGE_UPLOAD_DIR . $entity->getGalleryId() . "/";
		$imageInfo = getimagesize($imageDir.$entity->getFilename());
		$imageType = ($imageInfo[1] > $imageInfo[0]) ? "height" : "width";
		
		$this->addLabel("image_type",array(
			"soy2prefix" => $prefix,
			"text" => $imageType
		));
		
		$this->addLabel("image_type_wamei",array(
			"soy2prefix" => $prefix,
			"text" => ($imageType==="width") ? "yoko" : "tate"
		));
		
		$this->addLabel("url",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getUrl()
		));
		
		$this->addLink("url_link",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getUrl(),
			"link" => $entity->getUrl()
		));
		
		$this->addLabel("memo",array(
			"soy2prefix" => $prefix,
			"html" => nl2br($entity->getMemo())
		));
		
		$this->createAdd("create_date","DateLabel",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getCreateDate()
		));
		
		$this->createAdd("update_date","DateLabel",array(
			"soy2prefix" => $prefix,
			"text" => $entity->getUpdateDate()
		));
	}
}
?>