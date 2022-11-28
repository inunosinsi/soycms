<?php
class FixedPointGrantConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.fixed_point_grant.config.FixedPointGrantConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("FixedPointGrantConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "固定ポイント加算時の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "fixed_point_grant", "FixedPointGrantConfig");
