<?php
/*
 */
class YupackOrderCSVInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=yupack_order_csv").'">ゆうパックプリントRの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","yupack_order_csv","YupackOrderCSVInfo");
