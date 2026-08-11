<?php
class CommonPointGrantConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonPointGrantConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonPointGrantConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ポイント加算時の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_point_grant", "CommonPointGrantConfig");
?>