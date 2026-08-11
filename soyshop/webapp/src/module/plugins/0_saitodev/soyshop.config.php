<?php
class SaitodevConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.0_saitodev.config.SaitodevConfigPage");
		$form = SOY2HTMLFactory::createInstance("SaitodevConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "新機能紹介プラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "0_saitodev", "SaitodevConfig");
