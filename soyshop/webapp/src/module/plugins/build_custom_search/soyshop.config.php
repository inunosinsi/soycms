<?php
class BuildCustomSearchConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/BuildCustomSearchConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("BuildCustomSearchConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カスタム検索の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "build_custom_search", "BuildCustomSearchConfig");
