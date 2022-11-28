<?php

class GeneratePasswordConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.generate_password.config.GeneratePasswordConfigPage");
		$form = SOY2HTMLFactory::createInstance("GeneratePasswordConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "マイページログイン用パスワード自動生成プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "generate_password", "GeneratePasswordConfig");
