<?php

class BlogEditorConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token()){
			$checked = (isset($_POST["isWYGIWYG"]));
			$this->pluginObj->setIsWYSIWYG($checked);

			CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addCheckBox("is_wygiwyg", array(
			"name" => "isWYGIWYG",
			"value" => 1,
			"selected" => $this->pluginObj->getIsWYSIWYG(),
			"label" => "ブログの説明のテキストエリアでWYSIWYGエディタを利用する"
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
