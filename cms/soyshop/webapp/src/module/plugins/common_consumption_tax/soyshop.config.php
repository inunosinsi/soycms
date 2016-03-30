<?php
class CommonConsumptionTaxConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/config/CommonConsumptionTaxConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("CommonConsumptionTaxConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "消費税別表示設定";
	}
}
SOYShopPlugin::extension("soyshop.config","common_consumption_tax","CommonConsumptionTaxConfig");
?>