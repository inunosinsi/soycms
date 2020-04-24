<?php
/*
 */
class SOYCMSConnectorInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=soycms_connector").'">SOY CMS連携プラグインの設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","soycms_connector","SOYCMSConnectorInfo");
?>
