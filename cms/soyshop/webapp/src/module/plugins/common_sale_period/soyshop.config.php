<?php

class SalePeriodOptionConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/SalePeriodOptionConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SalePeriodOptionConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "セール価格期間設定プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_sale_period", "SalePeriodOptionConfig");
