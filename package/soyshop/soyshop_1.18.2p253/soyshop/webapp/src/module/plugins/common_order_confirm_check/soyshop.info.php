<?php
/*
 */
class CommonOrderConfirmCheckInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_order_confirm_check").'">入力内容確認の設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_order_confirm_check","CommonOrderConfirmCheckInfo");
?>