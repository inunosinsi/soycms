<?php

class PaymentStatusSortConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_status_sort.config.PaymentStatusSortConfigPage");
		$form = SOY2HTMLFactory::createInstance("PaymentStatusSortConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "支払い状況並び順の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "payment_status_sort", "PaymentStatusSortConfig");
