<?php

class IndexPage  extends MainMyPagePageBase{
	
	private $id;
	
	function __construct($args){
		if(!isset($args[0]) || !is_numeric($args[0])) $this->jump("login");
		$this->id = (int)$args[0];
		
		//プラグインをインストールしているかどうか
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("campaign")) $this->jump("login");
		
		$campaign = SOY2Logic::createInstance("module.plugins.campaign.logic.CampaignLogic")->getCampaignByIdWithinPostPeriod($this->id);
		
		//キャンペーンが期間外の場合も表示しない
		if(is_null($campaign->getId())) $this->jump("login");
		
		WebPage::__construct();
		
		$this->addLabel("campaign_title", array(
			"text" => $campaign->getTitle()
		));
		
		$this->addLabel("campaign_content", array(
			"html" => $campaign->getContent()
		));
	}
}
?>