<?php
class AttributeOrderTotalConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins." . $this->getModuleId() . ".config.AttributeOrderTotalConfigPage");
		$form = SOY2HTMLFactory::createInstance("AttributeOrderTotalConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "購入金額属性自動振り分けプラグイン";
	}
}
SOYShopPlugin::extension("soyshop.config", "attribute_order_total", "AttributeOrderTotalConfig");
?>
