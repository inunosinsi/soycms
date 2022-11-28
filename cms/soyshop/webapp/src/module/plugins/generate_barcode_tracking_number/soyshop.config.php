<?php

class GenerateBarcodeTrackingNumberConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.generate_barcode_tracking_number.config.GenerateBarcodeConfigPage");
		$form = SOY2HTMLFactory::createInstance("GenerateBarcodeConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "注文番号用バーコード生成プラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "generate_barcode_tracking_number", "GenerateBarcodeTrackingNumberConfig");
