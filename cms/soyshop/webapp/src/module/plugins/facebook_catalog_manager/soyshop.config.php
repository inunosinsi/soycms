<?php

class FacebookCatalogManagerConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.facebook_catalog_manager.config.FbCatalogConfigPage");
		$form = SOY2HTMLFactory::createInstance("FbCatalogConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Facebookカタログ用XML出力プラグインの使い方";
	}
}
SOYShopPlugin::extension("soyshop.config", "facebook_catalog_manager", "FacebookCatalogManagerConfig");
