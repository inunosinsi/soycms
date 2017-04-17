<?php

class CustomSearchChildListConfigPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		SOY2::import("module.plugins.custom_search_field.component.CustomSearchFieldListComponent");
		SOY2::import("module.plugins.custom_search_field_child_list.util.CustomSearchChildListUtil");
	}
	
	function execute(){
		WebPage::__construct();
		
		DisplayPlugin::toggle("no_installed_custom_search", !CustomSearchChildListUtil::checkInstalledCustomSearchField());
		DisplayPlugin::toggle("no_display_child_item", !CustomSearchChildListUtil::checkDisplayChildItemConfig());
		
		$this->createAdd("field_list", "CustomSearchFieldListComponent", array(
			"list" => CustomSearchFieldUtil::getConfig()
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>