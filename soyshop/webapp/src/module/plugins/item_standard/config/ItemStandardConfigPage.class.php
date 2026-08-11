<?php

class ItemStandardConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");
		SOY2::import("module.plugins.item_standard.component.StandardListComponent");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["id"]) && strlen($_POST["id"]) && isset($_POST["standard"]) && strlen($_POST["standard"])){
				$config = array("id" => trim($_POST["id"]), "standard" => trim($_POST["standard"]), "order" => null);
				$configs = ItemStandardUtil::getConfig();
				$configs[] = $config;

				ItemStandardUtil::saveConfig($configs);
				$this->configObj->redirect("created");
			}

			if(isset($_POST["update"])){
				$configs = $_POST["Config"];
				$orders = array();
				foreach($configs as $key => $config){
					$orders[$key] = (isset($config["order"]) && strlen($config["order"])) ? (int)$config["order"] : 9999;
				}
				array_multisort($orders , SORT_ASC , $configs);
				ItemStandardUtil::saveConfig($configs);
				$this->configObj->redirect("updated");
			}
		}
	}

	function execute(){
		if(isset($_GET["remove"])) self::remove();

		parent::__construct();

		DisplayPlugin::toggle("created", isset($_GET["created"]));
		DisplayPlugin::toggle("removed", isset($_GET["removed"]));

		$this->addForm("form");

		$this->createAdd("standard_list", "StandardListComponent", array(
			"list" => ItemStandardUtil::getConfig()
		));

		$this->addLabel("sample", array(
			"text" => self::buildSample()
		));
	}

	private function remove(){
		if(soy2_check_token()){
			$configs = ItemStandardUtil::getConfig();
			unset($configs[$_GET["remove"]]);
			$configs = array_values($configs);
			ItemStandardUtil::saveConfig($configs);
			$this->configObj->redirect("removed");
		}
	}

	private function buildSample(){
		$html = array();
		$html[] = "<!-- block:id=\"item\" -->";
		foreach(ItemStandardUtil::getConfig() as $conf){
			$html[] = "";
			$html[] = "<!-- cms:id=\"item_standard_" . $conf["id"] . "_show\" -->";
			$html[] = $conf["standard"] . ":" . "<select cms:id=\"item_standard_" . $conf["id"] . "\"></select>";
			$html[] = "<!-- /cms:id=\"item_standard_" . $conf["id"] . "_show\" -->";
		}
		$html[] = "";
		$html[] = "<!-- /block:id=\"item\" -->";
		return implode("\n", $html);
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
