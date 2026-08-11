<?php
class ItemStockManagerAdminList extends SOYShopAdminListBase{

	function getTabName(){
		return "在庫";
	}

	function getTitle(){
		return "在庫数一括変更と発送待ち件数";
	}

	function getContent(){
		SOY2::import("module.plugins.item_stock_manager.page.StockManagerPage");
		$form = SOY2HTMLFactory::createInstance("StockManagerPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "item_stock_manager", "ItemStockManagerAdminList");
