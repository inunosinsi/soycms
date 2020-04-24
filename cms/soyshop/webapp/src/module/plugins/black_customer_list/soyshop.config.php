<?php
class BlackCustomerListConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		SOY2::import("module.plugins.black_customer_list.config.BlackCustomerListConfigPage");
		$form = SOY2HTMLFactory::createInstance("BlackCustomerListConfigPage");
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
SOYShopPlugin::extension("soyshop.config", "black_customer_list", "BlackCustomerListConfig");