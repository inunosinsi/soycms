<?php
/*
 */
class CoineyInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_coiney").'">STORES決済(旧Coineyペイジ)支払いの設定画面へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "payment_coiney", "CoineyInfo");
