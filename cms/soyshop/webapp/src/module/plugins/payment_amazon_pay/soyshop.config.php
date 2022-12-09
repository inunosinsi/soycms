<?php
class AmazonPayConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_amazon_pay.config.AmazonPayConfigPage");
		$form = SOY2HTMLFactory::createInstance("AmazonPayConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Amazon Pay ワンタイムペイメントの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "payment_amazon_pay", "AmazonPayConfig");
