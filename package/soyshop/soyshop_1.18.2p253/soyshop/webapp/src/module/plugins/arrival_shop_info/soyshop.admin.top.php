<?php
class ArrivalShopInfoAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.ShopConfig");
	}

	function getLinkTitle(){
		return "ショップ設定";
	}

	function getTitle(){
		return "ショップ情報";
	}

	function getContent(){
		SOY2::import("module.plugins.arrival_shop_info.page.ShopInfoAreaPage");
		$form = SOY2HTMLFactory::createInstance("ShopInfoAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_shop_info", "ArrivalShopInfoAdminTop");
