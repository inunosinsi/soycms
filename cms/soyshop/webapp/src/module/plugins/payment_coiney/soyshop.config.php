<?php
class CoineyConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.payment_coiney.config.CoineyConfigPage");
		$form = SOY2HTMLFactory::createInstance("CoineyConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "STORES決済(Coineyペイジ)決済の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "payment_coiney", "CoineyConfig");
