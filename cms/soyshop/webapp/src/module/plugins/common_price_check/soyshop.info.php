<?php
/*
 */
class CommonPriceCheckInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_price_check").'">購入最低金額設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_price_check","CommonPriceCheckInfo");
?>