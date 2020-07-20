<?php
/*
 */
class AmazonPayInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_amazon_pay").'">Amazon Pay ワンタイムペイメントの設定画面へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "payment_amazon_pay", "AmazonPayInfo");
