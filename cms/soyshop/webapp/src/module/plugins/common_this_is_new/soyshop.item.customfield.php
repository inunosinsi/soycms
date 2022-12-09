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
			"visible" => ($item instanceof SOYShop_Item && self::_compareTime($item) > time()),
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
		));
	}

	function onDelete(int $itemId){}

	private function _compareTime(SOYShop_Item $item){
		static $d;
		if(is_null($d)){
			$cnf = SOYShop_DataSets::get("common_this_is_new", array("date" => 7));
			$d = (int)$cnf["d"];
		}
		return $item->getCreateDate() + ($d*60*60*24);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_this_is_new","CommonThisIsNew");
