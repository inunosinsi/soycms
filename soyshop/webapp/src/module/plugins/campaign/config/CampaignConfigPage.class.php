<?php

class CampaignConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.campaign.util.CampaignUtil");
		SOY2::imports("module.plugins.campaign.domain.*");
		SOY2::imports("module.plugins.campaign.component.*");
	}

	function execute(){
		parent::__construct();

		$this->createAdd("campaign_list", "CampaignListComponent", array(
			"list" => self::get()
		));
	}

	private function get(){
		$dao = self::dao();
		$dao->setOrder("post_period_start DESC");
		try{
			return $dao->get();
		}catch(Exception $e){
			return array();
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_CampaignDAO");
		return $dao;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
