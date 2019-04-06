<?php
class AddItemOrderStatusConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.add_itemorder_status.config.AddItemOrderStatusConfigPage");
		$form = SOY2HTMLFactory::createInstance("AddItemOrderStatusConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文詳細の商品毎の状態項目追加の設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "add_itemorder_status", "AddItemOrderStatusConfig");
