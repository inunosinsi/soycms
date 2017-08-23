<?php

class SOYCMSThumbnailConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Config"])){
			
			$config = $_POST["Config"];
			$this->pluginObj->setRatioW((int)$config["ratio_w"]);
			$this->pluginObj->setRatioH((int)$config["ratio_h"]);
			
			$this->pluginObj->setResizeW((int)$config["resize_w"]);
			$this->pluginObj->setResizeH((int)$config["resize_h"]);
			
			$this->pluginObj->setNoThumbnailPath(trim($config["no_thumbnail_path"]));
			
			$labels = array();
			if(count($config["label_thumbnail_path"])) foreach($config["label_thumbnail_path"] as $labelId => $path){
				if(strlen($path)) $labels[$labelId] = $path;
			}
			$this->pluginObj->setLabelThumbnailPaths($labels);
			
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		parent::__construct();
		
		$this->addForm("form");
		
		$this->addInput("ratio_w", array(
			"name" => "Config[ratio_w]",
			"value" => $this->pluginObj->getRatioW()
		));
		
		$this->addInput("ratio_h", array(
			"name" => "Config[ratio_h]",
			"value" => $this->pluginObj->getRatioH()
		));
		
		$this->addInput("resize_w", array(
			"name" => "Config[resize_w]",
			"value" => $this->pluginObj->getResizeW()
		));
		
		$this->addInput("resize_h", array(
			"name" => "Config[resize_h]",
			"value" => $this->pluginObj->getResizeH()
		));
		
		$this->addInput("no_thumbnail_path", array(
			"name" => "Config[no_thumbnail_path]",
			"value" => $this->pluginObj->getNoThumbnailPath(),
			"style" => "width:60%"
		));
		
		DisplayPlugin::toggle("display_noimage_ppreview_button",strlen($this->pluginObj->getNoThumbnailPath()));
		
		$labels = self::getLabels();
		DisplayPlugin::toggle("display_label_upload_area", count($labels));
		
		SOY2::imports("site_include.plugin.soycms_thumbnail.component.*");
		$this->createAdd("label_list", "LabelListComponent", array(
			"list" => $labels,
			"paths" => $this->pluginObj->getLabelThumbnailPaths()
		));
		
		$this->addLabel("site_id", array(
			"text" => UserInfoUtil::getSite()->getSiteId()
		));
		
		$this->addLabel("upload_file_path", array(
			"text" => SOY2PageController::createLink("Page.Editor.FileUpload")
		));
		
		$this->addLabel("im_resize_w", array(
			"text" => $this->pluginObj->getResizeW()
		));
	}
	
	private function getLabels(){
		try{
			return SOY2DAOFactory::create("cms.LabelDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>