<?php
class LoginWithAmazonConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.login_with_amazon.config.LoginWithAmazonConfigPage");
		$form = SOY2HTMLFactory::createInstance("LoginWithAmazonConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Login with Amazonの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "login_with_amazon", "LoginWithAmazonConfig");
