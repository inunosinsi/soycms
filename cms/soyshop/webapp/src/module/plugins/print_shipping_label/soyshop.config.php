<?php

class PrintShippingLabelConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.print_shipping_label.config.ShippingLabelConfigPage");
		$form = SOY2HTMLFactory::createInstance("ShippingLabelConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "配送伝票印刷プラグインの設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "print_shipping_label", "PrintShippingLabelConfig");
?>