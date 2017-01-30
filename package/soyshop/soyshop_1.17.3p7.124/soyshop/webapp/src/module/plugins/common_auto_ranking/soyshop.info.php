<?php
/*
 */
class CommonAutoRankingInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_auto_ranking") . '">自動ランキングの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "common_auto_ranking", "CommonAutoRankingInfo");
?>