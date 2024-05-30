<?php
class ShoppingMallConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.shopping_mall.config.ShoppingMallConfigPage");
		$form = SOY2HTMLFactory::createInstance("ShoppingMallConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "簡易ショッピングモール運営プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "shopping_mall", "ShoppingMallConfig");