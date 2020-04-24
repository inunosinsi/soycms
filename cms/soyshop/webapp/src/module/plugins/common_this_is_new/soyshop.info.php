<?php
/*
 */
class CommonThisIsNewInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_this_is_new").'">新着商品マーク表示プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_this_is_new","CommonThisIsNewInfo");
?>
