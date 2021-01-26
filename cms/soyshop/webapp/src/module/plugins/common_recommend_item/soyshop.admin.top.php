<?php
class RecommendItemAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Item");
	}

	function getLinkTitle(){
		return "商品管理";
	}

	function getTitle(){
		return "おすすめ商品";
	}

	function getContent(){
		SOY2::import("module.plugins.common_recommend_item.page.RecommendItemAreaPage");
		$form = SOY2HTMLFactory::createInstance("RecommendItemAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "common_recommend_item", "RecommendItemAdminTop");
