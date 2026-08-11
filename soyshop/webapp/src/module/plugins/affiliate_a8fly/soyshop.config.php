<?php
class AffiliateA8flyConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.affiliate_a8fly.config.AffiliateA8flyConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("AffiliateA8flyConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "A8FLYの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "affiliate_a8fly", "AffiliateA8flyConfig");
