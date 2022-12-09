<?php

class DeliveryAdminDummyConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.delivery_admin_dummy.config.DeliveryAdminDummyConfigPage");
		$form = SOY2HTMLFactory::createInstance("DeliveryAdminDummyConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "配送ダミーモジュール";
	}
}
SOYShopPlugin::extension("soyshop.config", "delivery_admin_dummy", "DeliveryAdminDummyConfig");
