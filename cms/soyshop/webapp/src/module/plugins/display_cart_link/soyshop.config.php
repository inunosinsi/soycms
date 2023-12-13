<?php
class DisplayCartLinkConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		include_once(dirname(__FILE__) . "/config/DisplayCartLinkConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("DisplayCartLinkConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カートリンク非表示プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "display_cart_link", "DisplayCartLinkConfig");