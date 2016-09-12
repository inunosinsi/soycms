<?php
/*
 */
class LoggingSlipNumberInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=logging_slip_number").'">伝票番号記録プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "logging_slip_number", "LoggingSlipNumberInfo");
?>
