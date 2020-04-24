<?php
/*
 */
class YuchoModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_yucho").'">ゆうちょ銀行の振替・払込み設定へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","payment_yucho","YuchoModuleInfo");
?>