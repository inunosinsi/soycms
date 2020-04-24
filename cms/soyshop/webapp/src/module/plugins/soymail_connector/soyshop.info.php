<?php
/*
 */
class SOYMailConnectorInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=soymail_connector").'">SOY Mail連携プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","soymail_connector","SOYMailConnectorInfo");
?>