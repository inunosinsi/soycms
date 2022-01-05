<?php
/*
 */
class OrderEditOnMyPageInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=order_edit_on_mypage").'">マイページで注文編集プラグインの設定画面へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "order_edit_on_mypage", "OrderEditOnMyPageInfo");
