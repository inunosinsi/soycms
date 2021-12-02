<?php
/*
 */
class AsyncCartButtonInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=async_cart_button").'">非同期カートボタンの設定方法</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "async_cart_button", "AsyncCartButtonInfo");
