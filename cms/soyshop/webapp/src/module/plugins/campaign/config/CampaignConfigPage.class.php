<?php

class CampaignConfigPage extends WebPage{
	
	private $configObj;
	
	function CampaignConfigPage(){
		SOY2::import("module.plugins.campaign.util.CampaignUtil");
	}
	
	function execute(){
		WebPage::WebPage();
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>