<?php
class LINELoginConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.line_login.config.LINELoginConfigPage");
		$form = SOY2HTMLFactory::createInstance("LINELoginConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "LINE Loginの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "line_login", "LINELoginConfig");
