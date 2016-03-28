<?php
class URLShortenerPluginFormPage extends WebPage{
	
	private $pluginObj;
	private $lastUpdate;
	
	function URLShortenerPluginFormPage(){
	}
	
	function doPost(){

    	if(soy2_check_token()){
			if(isset($_POST["url_shortener_form"])){
//				$this->pluginObj->setUseId($_POST["custom_alias_use_id"]);
				CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			}
			CMSPlugin::redirectConfigPage();
    	}	
    	
	}
	
	function execute(){
		WebPage::WebPage();

		$this->createAdd("url_shortener_form","HTMLForm",array(
		));
/*		
		$this->createAdd("custom_alias_prefix","HTMLInput",array(
			"name" => "custom_alias_prefix",
			"value" => $this->pluginObj->prefix,
		));
		$this->createAdd("custom_alias_postfix","HTMLInput",array(
			"name" => "custom_alias_postfix",
			"value" => $this->pluginObj->postfix,
		));

		$this->createAdd("use_id","HTMLCheckbox",array(
			"name" => "custom_alias_use_id",
			"value" => 1,
			"selected" => $this->pluginObj->useId,
			"label" => "常にIDをエイリアスの値にする（エイリアス入力欄は表示されません）。"
		));
*/

	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}

	function getTemplateFilePath(){
		return dirname(__FILE__)."/config_form.html";
	}

}

?>