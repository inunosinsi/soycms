<?php

class GoogleAnalyticsConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		include_once(dirname(__FILE__) . "/config/GoogleAnalyticsConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("GoogleAnalyticsConfigFormPage");
		$form->setConfigObj($this);

		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Google Analytics トラッキングコードの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "parts_google_analytics", "GoogleAnalyticsConfig");
?>