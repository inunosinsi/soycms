<?php

class OrderReRegisterConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.order_re_register.config.ReRegiserConfigPage");
		$form = SOY2HTMLFactory::createInstance("ReRegiserConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文再登録プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "order_re_register", "OrderReRegisterConfig");
