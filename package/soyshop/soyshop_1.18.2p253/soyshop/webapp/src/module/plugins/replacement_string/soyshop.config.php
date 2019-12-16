<?php

class ReplacementStringConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.replacement_string.config.ReplacementStringConfigPage");
		$form = SOY2HTMLFactory::createInstance("ReplacementStringConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "置換文字列生成プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "replacement_string", "ReplacementStringConfig");
