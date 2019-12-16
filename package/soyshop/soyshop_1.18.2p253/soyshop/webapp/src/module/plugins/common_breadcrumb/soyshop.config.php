<?php
class CommonBreadcrumbConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		include_once(dirname(__FILE__) . "/config/BreadcrumbConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("BreadcrumbConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "パンくずモジュール";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_breadcrumb", "CommonBreadcrumbConfig");
?>