<?php

class UtilMultiLanguageConfigFormPage extends WebPage{
	
	function UtilMultiLanguageConfigFormPage(){
		SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
		SOY2::import("site_include.plugin.util_multi_language.config.LanguageListComponent");
	}
	
	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			$this->pluginObj->setConfig($_POST["Config"]);
			$check = (isset($_POST["check_browser_language"])) ? (int)$_POST["check_browser_language"] : 0;
			$this->pluginObj->setCheckBrowserLanguage($check);
			CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
		}
		CMSPlugin::redirectConfigPage();
	}
	
	function execute(){
		WebPage::WebPage();
		
		$config = $this->pluginObj->getConfig();
		
		$this->addForm("form");
		
		$this->createAdd("language_list", "LanguageListComponent", array(
			"list" => SOYCMSUtilMultiLanguageUtil::allowLanguages(),
			"config" => $config,
			"smartPrefix" => self::getSmartPhonePrefix()
		));
		
		$this->addCheckBox("confirm_browser_language", array(
			"name" => "check_browser_language",
			"value" => 1,
			"selected" => $this->pluginObj->getCheckBrowserLanguage(),
			"label" => "確認する"
		));	
	}
	
	private function getSmartPhonePrefix(){
		//携帯振り分けプラグインがアクティブかどうか
		$pluginDao = SOY2DAOFactory::create("PluginDAO");
		try{
			$plugin = $pluginDao->getById("UtilMobileCheckPlugin");
		}catch(Exception $e){
			$plugin = new Plugin();
		}
		
		if(!$plugin->getIsActive()) return null;
		
		$obj = CMSPlugin::loadPluginConfig("UtilMobileCheckPlugin");
		if(is_null($obj)){
			$obj = new UtilMobileCheckPlugin;
		}
		
		return $obj->smartPrefix;
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>