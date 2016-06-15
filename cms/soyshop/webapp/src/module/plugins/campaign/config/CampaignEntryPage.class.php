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
		
		self::buildForm();
		
		$this->addLabel("insert_image_url", array(
			"text" => SOY2PageController::createLink("Site.File?display_mode=free")
		));
		
		$this->addLabel("insert_link_url", array(
			"text" => SOY2PageController::createLink("Site.Link?display_mode=free")
		));
		
		$this->addLabel("auto_save_url", array(
			"text" => SOY2PageController::createLink("Site.AutoSave.Save")
		));
		
		$this->addLabel("auto_load_url", array(
			"text" => SOY2PageController::createLink("Site.AutoSave.Load")
		));
				
		$this->addLabel("current_login_id", array(
			"text" => SOY2ActionSession::getUserSession()->getAttribute("loginid")
		));
		
		$this->addLabel("auto_save_js", array(
			"html" => "\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/post.js") . "\n"
		));
	}
	
	private function buildForm(){
		$this->addForm("form");
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