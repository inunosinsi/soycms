<?php
class CustomfieldReplacementStringConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.customfield_replacement_string.config.CustomReplaceConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomReplaceConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カスタムフィールド置換文字列プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "customfield_replacement_string", "CustomfieldReplacementStringConfig");
