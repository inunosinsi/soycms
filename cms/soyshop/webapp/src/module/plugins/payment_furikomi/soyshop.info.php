<?php
/*
 */
class FurikomiModuleInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_furikomi").'">銀行振込の口座情報などの設定へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "payment_furikomi", "FurikomiModuleInfo");
?>