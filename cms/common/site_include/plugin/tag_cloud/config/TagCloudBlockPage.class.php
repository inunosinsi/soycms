<?php
class TagCloudBlockPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			TagCloudUtil::saveConfig($_POST["Config"]);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$cnf = TagCloudUtil::getConfig();

		$this->addForm("form");

		$this->addInput("rank_divide", array(
			"name" => "Config[divide]",
			"value" => (isset($cnf["divide"])) ? (int)$cnf["divide"] : 10,
			"style" => "width:80px;"
		));

		$this->addTextArea("tags", array(
			"name" => "Config[tags]",
			"value" => (isset($cnf["tags"])) ? $cnf["tags"] : "",
		));
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
