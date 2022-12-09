<?php
class PaymentCustomConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_custom.config.PaymentCustomConfigPage");
		$form = SOY2HTMLFactory::createInstance("PaymentCustomConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カスタム支払モジュールの設定";
	}
}
SOYShopPlugin::extension("soyshop.config","payment_custom","PaymentCustomConfig");
