<?php
class LazyLoadConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.x_lazy_load.config.LazyLoadConfigPage");
		$form = SOY2HTMLFactory::createInstance("LazyLoadConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "LazyLoadプラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "x_lazy_load", "LazyLoadConfig");
