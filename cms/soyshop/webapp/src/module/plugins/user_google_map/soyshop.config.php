<?php
class UserGoogleMapConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.user_google_map.config.GoogleMapConfigPage");
		$form = SOY2HTMLFactory::createInstance("GoogleMapConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "顧客住所GoogleMaps連携プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "user_google_map", "UserGoogleMapConfig");
