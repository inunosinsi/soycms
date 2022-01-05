<?php
/*
 */
class CustomSearchFieldChildListInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=custom_search_field_child_list") . '">カスタムサーチフィールド(子商品一覧)</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "custom_search_field_child_list", "CustomSearchFieldChildListInfo");
?>