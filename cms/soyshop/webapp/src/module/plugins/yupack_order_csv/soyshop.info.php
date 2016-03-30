<?php
/*
 */
class YupackOrderCSVInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=yupack_order_csv").'">ゆうパックプリントの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","yupack_order_csv","YupackOrderCSVInfo");
?>
