<?php
class CommonConsumptionTaxConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["list"])){
			SOY2::import("module.plugins.common_consumption_tax.config.ReducedTaxRateShippingListPage");
			$form = SOY2HTMLFactory::createInstance("ReducedTaxRateShippingListPage");
		}else{
			SOY2::import("module.plugins.common_consumption_tax.config.CommonConsumptionTaxConfigFormPage");
			$form = SOY2HTMLFactory::createInstance("CommonConsumptionTaxConfigFormPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		if(isset($_GET["list"])){
			return "軽減税率対象商品一覧";
		}else{
			return "消費税別表示設定";
		}
	}
}
SOYShopPlugin::extension("soyshop.config","common_consumption_tax","CommonConsumptionTaxConfig");
