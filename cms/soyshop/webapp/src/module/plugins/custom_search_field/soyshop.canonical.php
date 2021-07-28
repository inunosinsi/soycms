<?php
/*
 */
class CustomSearchFieldCanonical extends SOYShopCanonicalBase{

	function canonical(){
		$args = soyshop_get_arguments();
		if(count($args) < 2) return null;
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		$cnfs = CustomSearchFieldUtil::getConfig();
		if(array_key_exists($args[0], $cnfs)){
			$alias = $args[0] . "/" . $args[1];
		}else{
			$alias = null;
		}
		unset($cnfs);

		return $alias;
	}
}
SOYShopPlugin::extension("soyshop.canonical", "custom_search_field", "CustomSearchFieldCanonical");
