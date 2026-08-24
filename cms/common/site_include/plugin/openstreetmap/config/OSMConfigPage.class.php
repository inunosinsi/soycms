<?php

class OSMConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.openstreetmap.util.OSMUtil");
		SOY2::import("site_include.plugin.openstreetmap.component.OSMIconListComponent");
	}
	
	function doPost(){
		if(isset($_POST["OSM_Update"])){
			$_POST["OSM"]["zoom"] = (int)$_POST["OSM"]["zoom"];
			$this->pluginObj->setHomePosition($_POST["OSM"]);
		}
	
		if(isset($_FILES["icon"]["name"]) && is_array($_FILES["icon"]["name"])){
			$cnt = count($_FILES["icon"]["name"]);
			if($cnt > 0){
				for($i = 0; $i < $cnt; $i++){
					
					$tmp = $_FILES["icon"]["tmp_name"][$i];

					if(is_uploaded_file($tmp)){
						$name = $_FILES["icon"]["name"][$i];
						$filepath = OSMUtil::iconDir().$name;
						move_uploaded_file($tmp, $filepath);
					}
				}
			}
		}

		if(isset($_POST["update"]) && isset($_POST["Config"])){
			$this->pluginObj->setIconConfig($_POST["Config"]);
		}
	
		CMSUtil::notifyUpdate();
		CMSPlugin::savePluginConfig(OSMPlugin::PLUGIN_ID, $this->pluginObj);
		CMSPlugin::redirectConfigPage();
	}
	
	function execute(){
		parent::__construct();

		if(isset($_GET["hash"]) && isset($_GET["soy2_token"])){
			if(soy2_check_token()){
				self::_delete($_GET["hash"]);
				SOY2PageController::jump("Plugin.Config?openstreetmap");
			}
		}

		self::_buildCenterPostionForm();
		self::_buildUploadForm();
	}

	private function _buildCenterPostionForm(){
		$cfg = $this->pluginObj->getHomePosition();
	
		$this->addForm("form");

		$this->addSelect("osm_zoom", array(
			"name" => "OSM[zoom]",
			"options" => range(0, 19),
			"selected" => (isset($cfg["zoom"]) && is_numeric($cfg["zoom"])) ? $cfg["zoom"] : 14
		));

		$this->addInput("osm_lat", array(
			"name" => "OSM[lat]",
			"value" => (isset($cfg["lat"])) ? $cfg["lat"] : "",
			"placeholder" => "00.00000"	
		));

		$this->addInput("osm_lng", array(
			"name" => "OSM[lng]",
			"value" => (isset($cfg["lng"])) ? $cfg["lng"] : "",
			"placeholder" => "000.00000"	
		));
	}

	private function _buildUploadForm(){
		$this->addForm("upload_form", array(
			"attr:enctype" => "multipart/form-data"
		));

		$icons = OSMUtil::getRegisteredIcons();
		DisplayPlugin::toggle("icon_configure", (count($icons)));

		$this->createAdd("icon_list", "OSMIconListComponent", array(
			"list" => OSMUtil::getRegisteredIcons(),
			"iconDir" => str_replace($_SERVER["DOCUMENT_ROOT"], "", OSMUtil::iconDir()),
			"siteId" => soycms_get_site_id_by_frontcontroller(),
			"iconConfig" => $this->pluginObj->getIconConfig()
		));
	}

	private function _delete(string $hash){
		$icons = OSMUtil::getRegisteredIcons();
		if(!count($icons)) return;

		foreach($icons as $icon){
			if($hash != OSMUtil::createHash($icon)) continue;

			unlink(OSMUtil::iconDir().$icon);
		}
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
