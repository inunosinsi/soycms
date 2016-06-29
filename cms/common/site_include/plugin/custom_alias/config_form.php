<?php
class CustomAliasPluginFormPage extends WebPage{
	
	private $pluginObj;
	
	function CustomAliasPluginFormPage(){
	}
	
	function doPost(){

    	if(soy2_check_token()){
			if(isset($_POST["custom_alias_use_id"])){
				if(isset($_POST["custom_alias_use_id"])) $this->pluginObj->setUseId($_POST["custom_alias_use_id"]);
				if(isset($_POST["custom_alias_prefix"])) $this->pluginObj->setPrefix($_POST["custom_alias_prefix"]);
				if(isset($_POST["custom_alias_postfix"])) $this->pluginObj->setPostfix($_POST["custom_alias_postfix"]);
				CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			}
			CMSPlugin::redirectConfigPage();
    	}	
    	
	}
	
	function execute(){
		WebPage::WebPage();

		$this->addForm("custom_alias_form");
		
		$this->addInput("custom_alias_prefix", array(
			"name" => "custom_alias_prefix",
			"value" => $this->pluginObj->prefix,
		));
		$this->addInput("custom_alias_postfix", array(
			"name" => "custom_alias_postfix",
			"value" => $this->pluginObj->postfix,
		));

		$this->addCheckBox("use_id", array(
			"name" => "custom_alias_use_id",
			"value" => 1,
			"selected" => $this->pluginObj->useId,
			"label" => "常にIDをエイリアスの値にする（エイリアス入力欄は表示されません）。"
		));

//		$this->createAdd("ignore","HTMLModel",array(
//			"visible" => false
//		));
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/config_form.html";
	}
}

?>