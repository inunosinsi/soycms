<?php
class ItemOrderPriceBulkChangeConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.itemorder_price_bulk_change.config.BulkChangeConfigPage");
		$form = SOY2HTMLFactory::createInstance("BulkChangeConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文商品の単価一括変更プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "itemorder_price_bulk_change", "ItemOrderPriceBulkChangeConfig");
