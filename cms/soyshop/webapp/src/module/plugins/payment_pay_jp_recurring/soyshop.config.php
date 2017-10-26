<?php
class PayJpRecurringConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_pay_jp_recurring.config.PayJpRecurringConfigPage");
		$form = SOY2HTMLFactory::createInstance("PayJpRecurringConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "PAY.JP定期課金の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "payment_pay_jp_recurring", "PayJpRecurringConfig");
