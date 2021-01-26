<?php
class AutoRankingAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.Detail?plugin=common_auto_ranking");
	}

	function getLinkTitle(){
		return "自動売上ランキングプラグインの設定";
	}

	function getTitle(){
		return "売上ランキング";
	}

	function getContent(){
		SOY2::import("module.plugins.common_auto_ranking.page.AutoRankingAreaPage");
		$form = SOY2HTMLFactory::createInstance("AutoRankingAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "common_auto_ranking", "AutoRankingAdminTop");
