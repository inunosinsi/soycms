<?php
class MaintenancePageConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.maintenance_page.config.MaintenancePageConfigPage");
		$form = SOY2HTMLFactory::createInstance("MaintenancePageConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "メンテナンスページ設置プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "maintenance_page", "MaintenancePageConfig");
