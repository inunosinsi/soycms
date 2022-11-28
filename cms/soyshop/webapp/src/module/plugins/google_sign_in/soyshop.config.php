<?php
class GoogleSignInConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.google_sign_in.config.SignInConfigPage");
		$form = SOY2HTMLFactory::createInstance("SignInConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Sign In With Googleの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "google_sign_in", "GoogleSignInConfig");
