<?php
class SOYCMSInfoConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){}
	
	function doPost(){
		
		if(isset($_POST["cache_clear_button"])){
			$this->pluginObj->clearCache();
		}elseif(isset($_POST["display_config_for_admin"]) && UserInfoUtil::isDefaultUser()){
			$this->pluginObj->updateDisplayConfig($_POST["display_config_for_admin"]);
			CMSUtil::notifyUpdate();
		}
		
		CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
		CMSPlugin::redirectConfigPage();
		
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->createAdd("display_admin_level_panel","HTMLModel",array(
			"visible"  => UserInfoUtil::isDefaultUser()
		));
		
		$this->createAdd("default_admin","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config_for_admin[".SOYCMS_Info_Plugin::DEFAULT_ADMIN."]",
			"value"    => 1,
			"selected" => $this->pluginObj->display_config_for_admin[SOYCMS_Info_Plugin::DEFAULT_ADMIN],
			"isBoolean"=> true,
			"label"    => "初期管理者",
		));
		$this->createAdd("site_admin","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config_for_admin[".SOYCMS_Info_Plugin::SITE_ADMIN."]",
			"value"    => 1,
			"selected" => $this->pluginObj->display_config_for_admin[SOYCMS_Info_Plugin::SITE_ADMIN],
			"isBoolean"=> true,
			"label"    => "一般管理者",
		));
		$this->createAdd("entry_admin","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config_for_admin[".SOYCMS_Info_Plugin::ENTRY_ADMIN."]",
			"value"    => 1,
			"selected" => $this->pluginObj->display_config_for_admin[SOYCMS_Info_Plugin::ENTRY_ADMIN],
			"isBoolean"=> true,
			"label"    => "記事管理者（公開権限あり）",
		));
		$this->createAdd("limited_admin","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config_for_admin[".SOYCMS_Info_Plugin::DRAFT_ENTRY_ADMIN."]",
			"value"    => 1,
			"selected" => $this->pluginObj->display_config_for_admin[SOYCMS_Info_Plugin::DRAFT_ENTRY_ADMIN],
			"isBoolean"=> true,
			"label"    => "記事管理者（公開権限無し）",
		));
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__)."/config.html";
	}

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
