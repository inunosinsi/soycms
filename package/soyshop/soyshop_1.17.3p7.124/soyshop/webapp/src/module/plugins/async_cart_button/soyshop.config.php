<?php
class AsyncCartButtonConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.async_cart_button.config.AsyncCartButtonConfigPage");
		$form = SOY2HTMLFactory::createInstance("AsyncCartButtonConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "非同期カートボタンの設定方法";
	}	

}
SOYShopPlugin::extension("soyshop.config", "async_cart_button", "AsyncCartButtonConfig");
?>