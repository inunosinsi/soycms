<?php
class SOYCMSServerInfoConfigPage extends WebPage{
	
	private $pluginObj;
	
	function SOYCMSServerInfoConfigPage(){
		
	}
	
	function doPost(){
		
		CMSUtil::notifyUpdate();
		CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
		CMSPlugin::redirectConfigPage();	
		
	}
	
	function execute(){
		WebPage::WebPage();
		
		include(SOY2::RootDir() . "error/error.func.php");
		
		$this->createAdd("server_info", "HTMLTextArea", array(
			"text" => get_soycms_report() ."\n\n". get_soycms_options() ."\n\n". get_environment_report(),
			"style" => "width:100%;height:1000px;",
			"readonly" => "readonly"
		));
		$this->createAdd("php_info", "HTMLModel", array(
			"src" => SOY2PageController::createLink("Plugin.Config") ."?".$this->pluginObj->getId()."&phpinfo",
			"style" => "display:none;width:100%;height:1000px;",
		));
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__)."/config.html";
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
