<?php

class PaymentConstructionConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_construction.config.ConstructionConfigPage");
		$form = SOY2HTMLFactory::createInstance("ConstructionConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "施工用手数料設定の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "payment_construction", "PaymentConstructionConfig");
