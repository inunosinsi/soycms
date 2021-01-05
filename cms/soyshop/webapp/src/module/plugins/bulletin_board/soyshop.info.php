<?php
/*
 */
class BulletinBoardInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=bulletin_board") . '">SOY Board on SOY Shopの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "bulletin_board", "BulletinBoardInfo");
