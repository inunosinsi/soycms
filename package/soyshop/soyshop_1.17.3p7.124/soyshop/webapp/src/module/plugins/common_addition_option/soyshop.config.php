<?php
class CommonAdditionOptionConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonAdditionOptionConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonAdditionOptionConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "加算オプションプラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_addition_option", "CommonAdditionOptionConfig");
?>