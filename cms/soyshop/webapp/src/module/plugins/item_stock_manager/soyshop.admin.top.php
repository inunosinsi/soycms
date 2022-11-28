<?php
class ItemStockManagerAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		//return SOY2PageController::createLink("Config.ShopConfig");
	}

	function getLinkTitle(){
		//return "";
	}

	function getTitle(){
		//return "";
	}

	function getContent(){
//		SOY2::import("module.plugins.reserve_calendar.page.admin.ReserveCalendarInfoPage");
//		$form = SOY2HTMLFactory::createInstance("ReserveCalendarInfoPage");
//		$form->setConfigObj($this);
//		$form->execute();
//		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "item_stock_manager", "ItemStockManagerAdminTop");
