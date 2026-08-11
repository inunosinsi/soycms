<?php
class DepositManagerConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.deposit_manager.config.DepositManagerConfigPage");
		$form = SOY2HTMLFactory::createInstance("DepositManagerConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "入金管理設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "deposit_manager", "DepositManagerConfig");
