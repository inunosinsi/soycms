<?php
/*
 */
class CommonTicketBaseInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_ticket_base") . '">チケットの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_ticket_base", "CommonTicketBaseInfo");
