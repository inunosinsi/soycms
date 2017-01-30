<?php
/*
 */
class CommonNewItemInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_new_item").'">新着商品の表示設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_new_item","CommonNewItemInfo");
?>
