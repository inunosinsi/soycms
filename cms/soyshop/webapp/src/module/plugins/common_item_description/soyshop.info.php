<?php
/*
 */
class CommonItemDescriptionInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_item_description").'">商品詳細情報追加設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_item_description","CommonItemDescriptionInfo");
?>