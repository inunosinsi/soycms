<?php

class CustomSearchConfigPage extends WebPage{

	private $configObj;
	private $config;

	function __construct(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		$this->config = CustomSearchFieldUtil::getSearchConfig();
	}

	function doPost(){

		if(soy2_check_token()){
			CustomSearchFieldUtil::saveSearchConfig($_POST["Config"]);
			$this->configObj->redirect("config&updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addLabel("nav", array(
			"html" => LinkNaviAreaComponent::build()
		));

		$this->addForm("form");

		$this->addCheckBox("search_type_single", array(
			"name" => "Config[search][single]",
			"value" => 1,
			"selected" => (isset($this->config["search"]["single"]) && $this->config["search"]["single"] == 1),
			"label" => "通常商品を表示する"
		));

		$this->addCheckBox("search_type_parent", array(
			"name" => "Config[search][parent]",
			"value" => 1,
			"selected" => (isset($this->config["search"]["parent"]) && $this->config["search"]["parent"] == 1),
			"label" => "商品グループの親商品を表示する"
		));

		$this->addCheckBox("search_type_child", array(
			"name" => "Config[search][child]",
			"value" => 1,
			"selected" => (isset($this->config["search"]["child"]) && $this->config["search"]["child"] == 1),
			"label" => "商品グループの子商品を表示する"
		));

		$this->addCheckBox("search_type_download", array(
			"name" => "Config[search][download]",
			"value" => 1,
			"selected" => (isset($this->config["search"]["download"]) && $this->config["search"]["download"] == 1),
			"label" => "ダウンロード商品を表示する"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
