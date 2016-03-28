<?php

class SOYShopItemImportConfigFormPage extends WebPage{
	
	private $pluginObj;
	private $configLogic;
	
	function SOYShopItemImportConfigFormPage(){
		$this->configLogic = SOY2Logic::createInstance("site_include.plugin.soyshop_item_import.logic.ConfigLogic");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			
			//キャッシュを捨てる
			$root = dirname(SOY2::RootDir());
			CMSUtil::unlinkAllIn($root . "/soycms/cache/");
			CMSUtil::unlinkAllIn($root . "/soyshop/cache/");
				
			$this->pluginObj->setSiteId($_POST["Config"]["siteId"]);
						
			CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$this->addSelect("shop_list", array(
			"name" => "Config[siteId]",
			"options" => $this->configLogic->getList(),
			"selected" => $this->pluginObj->getSiteId()
		));
	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>