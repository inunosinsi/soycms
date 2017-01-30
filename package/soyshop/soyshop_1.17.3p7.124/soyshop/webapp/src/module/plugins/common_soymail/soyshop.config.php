<?php
class CommonSoymailConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonSoymailConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonSoymailConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "SOY Mail連携の設定";
	}
}
SOYShopPlugin::extension("soyshop.config","common_soymail","CommonSoymailConfig");
?>