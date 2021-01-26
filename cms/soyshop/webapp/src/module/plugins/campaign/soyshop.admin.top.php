<?php
class CampaignAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.Detail?plugin=campaign");
	}

	function getLinkTitle(){
		return "キャンペーンプラグイン";
	}

	function getTitle(){
		return "キャンペーン";
	}

	function getContent(){
		SOY2::import("module.plugins.campaign.page.CampaignAreaPage");
		$form = SOY2HTMLFactory::createInstance("CampaignAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "campaign", "CampaignAdminTop");
