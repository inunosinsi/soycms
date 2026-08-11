<?php
class UtilMobileCheckConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/UtilMobileCheckConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("UtilMobileCheckConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "携帯自動振り分け設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "util_mobile_check", "UtilMobileCheckConfig");
