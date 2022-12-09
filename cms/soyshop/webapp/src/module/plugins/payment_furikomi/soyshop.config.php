<?php

class PaymentFurikomiConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_furikomi.config.PaymentFurikomiConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("PaymentFurikomiConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "銀行振込の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "payment_furikomi", "PaymentFurikomiConfig");
?>