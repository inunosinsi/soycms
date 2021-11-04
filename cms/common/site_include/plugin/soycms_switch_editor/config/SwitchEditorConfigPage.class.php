<?php

class SwitchEditorConfigPage extends WebPage {

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token()){
			//WYSIWYGの選択
			if(isset($_POST["WYSIWYGConfig"])){
				$this->pluginObj->setWYSIWYGConfig($_POST["WYSIWYGConfig"]);
			}

			//各ラベルの設定
			if(isset($_POST["labelConfig"])){
				$this->pluginObj->setLabelConfig($_POST["labelConfig"]);
			}

			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		//WYSIWYG
		$this->addSelect("editor_select", array(
			"options" => self::_editors(),
			"name" => "WYSIWYGConfig",
			"selected" => $this->pluginObj->getWYSIWYGConfig()
		));

		SOY2::import("site_include.plugin.soycms_switch_editor.component.LabelConfigListComponent");
		$this->createAdd("labels", "LabelConfigListComponent", array(
			"list" => SOY2DAOFactory::create("cms.LabelDAO")->get(),
			"config" => $this->pluginObj->getLabelConfig()
		));
	}

	private function _editors(){
		return array(
			"tinyMCE" => "tinyMCE",
//			"CKEditor" => "CKEditor",
			"plain" => "WYSIWYGを無効",
		);
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
