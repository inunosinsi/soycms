<?php
class AddItemOrderFlagConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.add_itemorder_flag.config.AddItemOrderFlagConfigPage");
		$form = SOY2HTMLFactory::createInstance("AddItemOrderFlagConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文詳細の商品毎のフラグ項目追加の設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "add_itemorder_flag", "AddItemOrderFlagConfig");
