<?php

class CampaignAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
		
		SOY2::imports("module.plugins.campaign.domain.*");
		$campaigns = SOY2DAOFactory::create("SOYShop_CampaignDAO")->getBeforePostPeriodEnd(6);
		
		DisplayPlugin::toggle("more_campaign", (count($campaigns) > 5));
		DisplayPlugin::toggle("has_campaign", (count($campaigns) > 0));
		DisplayPlugin::toggle("no_campaign", (count($campaigns) === 0));
		
		$campaigns = array_slice($campaigns, 0, 5);
		
		SOY2::imports("module.plugins.campaign.component.*");
		$this->createAdd("campaign_list", "CampaignListComponent", array(
			"list" => $campaigns
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>