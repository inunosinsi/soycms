<?php
class SlipNumberConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		SOY2::import("module.plugins.slip_number.config.SlipNumberConfigPage");
		$form = SOY2HTMLFactory::createInstance("SlipNumberConfigPage");
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
SOYShopPlugin::extension("soyshop.config", "slip_number", "SlipNumberConfig");