<?php
/*
 */
class ItemListRandomInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=item_list_random") . '">商品のランダム表示の設定方法</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "item_list_random", "ItemListRandomInfo");
?>