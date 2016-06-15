<?php

class CampaignEntryPage extends WebPage{
	
	private $configObj;
	private $id;
	
	function CampaignEntryPage(){
		SOY2::import("module.plugins.campaign.util.CampaignUtil");
		SOY2::imports("module.plugins.campaign.domain.*");
	}
	
	function execute(){
		$this->id = (isset($_GET["id"])) ? $_GET["id"] : null;
		
		WebPage::WebPage();
		
		$campaign = self::getCampaign();
		
		$this->addLabel("insert_image_url", array(
			"text" => SOY2PageController::createLink("Site.File?display_mode=free")
		));
		
		$this->addLabel("insert_link_url", array(
			"text" => SOY2PageController::createLink("Site.Link?display_mode=free")
		));
	}
	
	private function getCampaign(){
		try{
			return SOY2DAOFactory::create("SOYShop_CampaignDAO")->getById($this->id);
		}catch(Exception $e){
			return new SOYShop_Campaign();
		}
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>