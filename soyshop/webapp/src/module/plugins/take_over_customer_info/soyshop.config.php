<?php
class TakeOverCustomerInfoConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.take_over_customer_info.config.TakeOverCustomerConfigPage");
		$form = SOY2HTMLFactory::createInstance("TakeOverCustomerConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "別サイト顧客情報引継ぎプラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "take_over_customer_info", "TakeOverCustomerInfoConfig");
