<?php
class ArrivalItemStockAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return (AUTH_CONFIG) ? SOY2PageController::createLink("Config.ShopConfig") : "";
	}

	function getLinkTitle(){
		return (AUTH_CONFIG) ? "ショップ設定" : "";
	}

	function getTitle(){
		return "在庫切れ商品";
	}

	function getContent(){
		SOY2::import("module.plugins.arrival_item_stock.page.ItemStockAreaPage");
		$form = SOY2HTMLFactory::createInstance("ItemStockAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_item_stock", "ArrivalItemStockAdminTop");
