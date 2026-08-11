<?php
class AddressItemsConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.address_items.config.AddressItemsConfigPage");
		$form = SOY2HTMLFactory::createInstance("AddressItemsConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "住所項目の設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "address_items", "AddressItemsConfig");
