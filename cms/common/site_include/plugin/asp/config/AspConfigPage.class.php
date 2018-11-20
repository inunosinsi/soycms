<?php

class AspConfigPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.asp.util.AspUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			foreach(array("pre", "register") as $t){
				AspUtil::saveMailConfig($t, $_POST["Mail"][$t]);
			}
		}
	}

	function execute(){
		parent::__construct();

		$this->addLabel("config_file_dir", array(
			"text" => SOY2::RootDir() . "config/"
		));

		$this->addForm("form");

		foreach(array("pre", "register") as $t){
			$mail = AspUtil::getMailConfig($t);

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

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
