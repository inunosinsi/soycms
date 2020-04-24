<?php
class FixedFormModuleConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.fixed_form_module.config.FixedFormModuleConfigPage");
		$form = SOY2HTMLFactory::createInstance("FixedFormModuleConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品毎パーツモジュール選択読み込みプラグインの使用方法";
	}

}
SOYShopPlugin::extension("soyshop.config", "fixed_form_module", "FixedFormModuleConfig");
