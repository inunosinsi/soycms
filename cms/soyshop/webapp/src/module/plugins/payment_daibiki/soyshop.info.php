<?php
/*
 */
class DaibikiModuleInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=payment_daibiki").'">代引き手数料の設定画面へ</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","payment_daibiki","DaibikiModuleInfo");
?>