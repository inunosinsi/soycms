<?php
/*
 */
class CommonSoymailInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
//		if($active){
//			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_soymail").'">SOY Mail連携の設定</a>';
//		}else{
//			return "";
//		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_soymail","CommonSoymailInfo");
?>
