<?php
/*
 */
class SOYInquiryConnectorInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=soyinquiry_connector").'">SOY Inquiry連携プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "soyinquiry_connector", "SOYInquiryConnectorInfo");
