<?php
class PayJpConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_pay_jp.config.PayJpConfigPage");
		$form = SOY2HTMLFactory::createInstance("PayJpConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "PAY.JPクレジットカード決済の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "payment_pay_jp", "PayJpConfig");
