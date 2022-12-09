<?php
class HTMLCacheConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.x_html_cache.config.HTMLCacheConfigPage");
		$form = SOY2HTMLFactory::createInstance("HTMLCacheConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "HTMLキャッシュプラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "x_html_cache", "HTMLCacheConfig");
