<?php

class ProsperityReportConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.prosperity_report.config.ProsperityReportConfigPage");
		$form = SOY2HTMLFactory::createInstance("ProsperityReportConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "繁盛レポートプラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "prosperity_report", "ProsperityReportConfig");
?>