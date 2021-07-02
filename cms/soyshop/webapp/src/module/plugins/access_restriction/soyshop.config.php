<?php
class AccessRestrictionConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.access_restriction.config.AccessRestrictionConfigPage");
		$form = SOY2HTMLFactory::createInstance("AccessRestrictionConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "アクセス制限プラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config", "access_restriction", "AccessRestrictionConfig");
