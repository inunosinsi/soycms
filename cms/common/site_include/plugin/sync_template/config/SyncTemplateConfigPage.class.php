<?php

class SyncTemplateConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			//　設定の変更
			if(isset($_POST["config"])){
				$convertURL = (isset($settings["convert_url"]) && $settings["convert_url"] == 1);
				$this->pluginObj->setConvertURL($convertURL);

				$autoImport = (isset($settings["auto_import"]) && $settings["auto_import"] == 1);
				$this->pluginObj->setAutoImport($autoImport);
				
				$this->useExtPhpIfPhpAllowed = (isset($settings["useExtPhpIfPhpAllowed"]) && $settings["useExtPhpIfPhpAllowed"] == 1);
				$this->pluginObj->setUseExtPhpIfPhpAllowed($useExtPhpIfPhpAllowed);

				$this->zeroPaddingWidth = (int)$settings["zeroPaddingWidth"];
				$this->pluginObj->setZeroPaddingWidth($zeroPaddingWidth);
				
				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this);
				CMSPlugin::redirectConfigPage();
			}
		}
	}

	function execute(){
		parent::__construct();
	}

	function setPluginObj(SyncTemplatePlugin $pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
