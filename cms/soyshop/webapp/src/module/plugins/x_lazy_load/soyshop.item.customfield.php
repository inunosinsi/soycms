<?php

class LazyLoadCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){}
	function getForm(SOYShop_Item $item){}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		static $counter;
		if(is_null($counter)) $counter = 0;

		if(!is_null($item->getId())) $counter++;

		$isLazyLoad = ($counter > self::_getCountConfig());

		$htmlObj->addLabel("lazy_load_value", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => ($isLazyLoad) ? "lazy" : "auto"
		));

		$img = (!is_null($item->getId())) ? soyshop_convert_file_path($item->getAttribute("image_small"), $item) : "";
		$htmlObj->addImage("lazy_load_item_small_image", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
            "src" => $img,
			"attr:loading" => ($isLazyLoad) ? "lazy" : "auto",
            "visible" => (strlen($img) > 0)
        ));
	}

	private function _getCountConfig(){
		static $c;
		if(is_null($c)){
			SOY2::import("module.plugins.x_lazy_load.util.LazyLoadUtil");
			$cnf = LazyLoadUtil::getConfig();
			$c = (isset($cnf["count"]) && is_numeric($cnf["count"]) && $cnf["count"] > 0) ? $cnf["count"] : 3;
		}
		return $c;
	}

	function onDelete($id){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "x_lazy_load", "LazyLoadCustomField");
