<?php

class ChangeOrderStatusInvalidConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.change_order_status_invalid.config.OrderStatusInvalidConfigPage");
		$form = SOY2HTMLFactory::createInstance("OrderStatusInvalidConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "自動注文無効プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "change_order_status_invalid", "ChangeOrderStatusInvalidConfig");
