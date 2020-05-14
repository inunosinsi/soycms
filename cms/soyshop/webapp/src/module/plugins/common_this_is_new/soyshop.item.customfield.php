<?php
/*
 */
class CommonThisIsNew extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){}

	function getForm(SOYShop_Item $item){}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){

		$htmlObj->addModel("this_is_new", array(
			"visible" => (isset($item) && self::compareTime($item) > time()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
		));
	}

	function onDelete($id){}

	private function compareTime(SOYShop_Item $item){
		$config = SOYShop_DataSets::get("common_this_is_new", array("date" => 7));
		return $item->getCreateDate() + $config["date"] * 60*60*24;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_this_is_new","CommonThisIsNew");
