<?php
class AddPaymentStatusConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.add_payment_status.config.AddPaymentStatusConfigPage");
		$form = SOY2HTMLFactory::createInstance("AddPaymentStatusConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "支払い状況項目追加の設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "add_payment_status", "AddPaymentStatusConfig");
