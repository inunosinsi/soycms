<?php
class FacebookLoginConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.facebook_login.config.FacebookLoginConfigPage");
		$form = SOY2HTMLFactory::createInstance("FacebookLoginConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Facebookログインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "facebook_login", "FacebookLoginConfig");
