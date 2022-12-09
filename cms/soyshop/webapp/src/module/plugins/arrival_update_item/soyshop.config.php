<?php
class ArrivalUpdateItemConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.arrival_update_item.config.ArrivalUpdateItemConfigPage");
		$form = SOY2HTMLFactory::createInstance("ArrivalUpdateItemConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "最近更新した商品表示プラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "arrival_update_item", "ArrivalUpdateItemConfig");
