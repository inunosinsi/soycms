<?php
class CustomSearchFieldChildListConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.custom_search_field_child_list.config.CustomSearchChildListConfigPage");
		$form = SOY2HTMLFactory::createInstance("CustomSearchChildListConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "カスタムサーチフィールド(子商品一覧)";
	}
}
SOYShopPlugin::extension("soyshop.config", "custom_search_field_child_list", "CustomSearchFieldChildListConfig");
