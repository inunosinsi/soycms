<?php
class LoggingBlackCustomerConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		SOY2::import("module.plugins.logging_black_customer.config.LoggingBlackCustomerConfigPage");
		$form = SOY2HTMLFactory::createInstance("LoggingBlackCustomerConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ブラック顧客リストプラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "logging_black_customer", "LoggingBlackCustomerConfig");