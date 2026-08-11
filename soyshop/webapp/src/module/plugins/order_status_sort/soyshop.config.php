<?php

class OrderStatusSortConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.order_status_sort.config.OrderStatusSortConfigPage");
		$form = SOY2HTMLFactory::createInstance("OrderStatusSortConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文状態並び順の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "order_status_sort", "OrderStatusSortConfig");
