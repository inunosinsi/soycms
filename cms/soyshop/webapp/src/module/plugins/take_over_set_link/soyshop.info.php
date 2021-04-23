<?php
/*
 */
class TakeOverSetLinkInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=take_over_set_link").'">別サイト顧客情報引継ぎ用リンク設置プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "take_over_set_link", "TakeOverSetLinkInfo");
