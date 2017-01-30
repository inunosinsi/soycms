<?php
/*
 */
class DisplayCartLinkInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=display_cart_link") . '">カートに入れるリンク非表示プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "display_cart_link", "DisplayCartLinkInfo");
?>