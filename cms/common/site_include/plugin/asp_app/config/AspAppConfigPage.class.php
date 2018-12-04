<?php

class AspAppConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.asp_app.util.AspAppUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			AspAppUtil::saveAppIdConfig($_POST["AppId"]);
			foreach(array("pre", "register") as $t){
				AspAppUtil::saveMailConfig($t, $_POST["Mail"][$t]);
			}

		}
	}

	function execute(){
		parent::__construct();

		$this->addLabel("config_file_dir", array(
			"text" => SOY2::RootDir() . "config/"
		));

		$this->addForm("form");

		$this->addSelect("app_id", array(
			"name" => "AppId",
			"options" => self::getAppList(),
			"selected" => AspAppUtil::getAppIdConfig()
		));

		foreach(array("pre", "register") as $t){
			$mail = AspAppUtil::getMailConfig($t);

			$this->addInput($t . "_title", array(
				"name" => "Mail[" . $t . "][title]",
				"value" => $mail["title"]
			));

			$this->addTextArea($t . "_content", array(
				"name" => "Mail[" . $t . "][content]",
				"value" => $mail["content"]
			));
		}
	}

	private function getAppList(){
		$apps = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic")->getLoginiableApplicationLists();
		if(!count($apps)) return array();

		$list = array();
		foreach($apps as $app){
			$list[$app["id"]] = $app["title"];
		}
		return $list;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
