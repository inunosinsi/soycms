<?php
/*
 */
class CommonPurchaseCheckInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_purchase_check") . '">購入済み商品チェックプラグイン設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "common_purchase_check", "CommonPurchaseCheckInfo");
?>