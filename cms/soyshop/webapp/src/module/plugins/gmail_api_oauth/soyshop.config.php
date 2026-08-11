<?php
class GmailAPIOAuthConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.gmail_api_oauth.config.OAuthConfigPage");
		$form = SOY2HTMLFactory::createInstance("OAuthConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Gmail API(OAuth2.0認証)プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "gmail_api_oauth", "GmailAPIOAuthConfig");
