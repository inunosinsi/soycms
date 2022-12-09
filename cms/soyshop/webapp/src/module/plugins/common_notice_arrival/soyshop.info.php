<?php
/*
 */
class CommonNoticeArrivalInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_notice_arrival").'">入荷通知設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_notice_arrival", "CommonNoticeArrivalInfo");
