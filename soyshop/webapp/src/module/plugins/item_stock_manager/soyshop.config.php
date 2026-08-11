<?php
class ItemStockManagerConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.item_stock_manager.config.ItemCodeStringWithStockPage");
		$form = SOY2HTMLFactory::createInstance("ItemCodeStringWithStockPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品コード - 在庫一括 CSV登録";
	}
}
SOYShopPlugin::extension("soyshop.config", "item_stock_manager", "ItemStockManagerConfig");
