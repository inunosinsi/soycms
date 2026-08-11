<?php
class ItemDetailConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.parts_item_detail.config.PartsItemDetailConfigPage");
		$form = SOY2HTMLFactory::createInstance("PartsItemDetailConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品詳細設定プラグインの設定方法";
	}
}
SOYShopPlugin::extension("soyshop.config", "parts_item_detail", "ItemDetailConfig");
