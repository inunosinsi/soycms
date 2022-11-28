<?php
class CommonRelativeItemConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		SOY2::import("module.plugins.common_relative_item.config.RelativeItemConfigPage");
		$form = SOY2HTMLFactory::createInstance("RelativeItemConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "関連商品";
	}

}
SOYShopPlugin::extension("soyshop.config","common_relative_item","CommonRelativeItemConfig");
