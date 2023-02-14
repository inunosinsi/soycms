<?php

class InstaGraphConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.instagram_graph_api.util.InstagramGraphAPIUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Config"])){
				InstagramGraphAPIUtil::saveConfig($_POST["Config"]);
				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		parent::__construct();

		$cnf = InstagramGraphAPIUtil::getConfig();

		$this->addForm("form");

		$this->addInput("ver", array(
			"name" => "Config[ver]",
			"value" => $cnf["ver"],
			"placeholder" => "12.0"
		));

		$this->addInput("bizId", array(
			"name" => "Config[bizId]",
			"value" => $cnf["bizId"]
		));

		$this->addInput("token", array(
			"name" => "Config[token]",
			"value" => $cnf["token"]
		));

		$this->addInput("limit", array(
			"name" => "Config[limit]",
			"value" => $cnf["limit"]
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}