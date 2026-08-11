<?php

class GenerateBarcodeItemJanCodeConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.generate_barcode_item_jan_code.config.GenerateJancodeConfigPage");
		$form = SOY2HTMLFactory::createInstance("GenerateJancodeConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "商品JANコード用バーコード生成";
	}
}
SOYShopPlugin::extension("soyshop.config", "generate_barcode_item_jan_code", "GenerateBarcodeItemJanCodeConfig");
