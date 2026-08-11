<?php
class ReturnsSlipNumberConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		SOY2::import("module.plugins.returns_slip_number.config.ReturnsSlipNumberConfigPage");
		$form = SOY2HTMLFactory::createInstance("ReturnsSlipNumberConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "返送伝票番号記録プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "returns_slip_number", "ReturnsSlipNumberConfig");
