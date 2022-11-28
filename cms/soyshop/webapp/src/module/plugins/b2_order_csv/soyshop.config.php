<?php
class B2OrderCSVConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.b2_order_csv.config.B2OrderCsvConfigPage");
		$form = SOY2HTMLFactory::createInstance("B2OrderCsvConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "B2の設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "b2_order_csv", "B2OrderCSVConfig");
