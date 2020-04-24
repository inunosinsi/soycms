<?php

class CustomSearchChildListUtil{

	public static function checkInstalledCustomSearchField(){
		SOY2::import("util.SOYShopPluginUtil");
		return SOYShopPluginUtil::checkIsActive("custom_search_field");
	}

	public static function checkDisplayChildItemConfig(){
		SOY2::import("domain.config.SOYShop_ShopConfig");
		return SOYShop_ShopConfig::load()->getDisplayChildItem();
	}
}
