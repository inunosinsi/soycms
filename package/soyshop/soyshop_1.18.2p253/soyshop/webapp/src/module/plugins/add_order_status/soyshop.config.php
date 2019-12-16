<?php
class AddOrderStatusConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.add_order_status.config.AddOrderStatusConfigPage");
		$form = SOY2HTMLFactory::createInstance("AddOrderStatusConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文状態項目追加の設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "add_order_status", "AddOrderStatusConfig");
