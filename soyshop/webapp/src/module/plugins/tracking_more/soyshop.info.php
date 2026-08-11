<?php
/*
 */
class TrackingMoreInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=tracking_more").'">Trackingmoreの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "tracking_more", "TrackingMoreInfo");
