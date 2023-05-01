<?php
class ArrivalNewOrderConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.arrival_new_order.config.ArrivalNewOrderConfigPage");
		$form = SOY2HTMLFactory::createInstance("ArrivalNewOrderConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "新着注文一覧表示プラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "arrival_new_order", "ArrivalNewOrderConfig");
