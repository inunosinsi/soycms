<?php

class reCAPTCHAv3Config extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.reCAPTCHAv3.config.reCAPTCHAv3ConfigPage");
		$form = SOY2HTMLFactory::createInstance("reCAPTCHAv3ConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "Google reCAPTCHA v3の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "reCAPTCHAv3", "reCAPTCHAv3Config");
