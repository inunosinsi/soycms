<?php
/*
 */
class SOYShopSortButtonInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_sort_button") . '">ソートボタンの設置方法</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_sort_button", "SOYShopSortButtonInfo");
?>