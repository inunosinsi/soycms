<?php
class AttributeOrderCountConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins." . $this->getModuleId() . ".config.AttributeOrderCountConfigPage");
		$form = SOY2HTMLFactory::createInstance("AttributeOrderCountConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "購入回数属性自動振り分けプラグイン";
	}	
}
SOYShopPlugin::extension("soyshop.config", "attribute_order_count", "AttributeOrderCountConfig");
?>