<?php
class LoggingSlipNumberConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		SOY2::import("module.plugins.logging_slip_number.config.LoggingSlipNumberConfigPage");
		$form = SOY2HTMLFactory::createInstance("LoggingSlipNumberConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "伝票番号記録プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "logging_slip_number", "LoggingSlipNumberConfig");