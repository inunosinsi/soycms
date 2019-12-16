<?php
/*
 */
class ButtonSocialInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=button_social") . '">ソーシャルボタンの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "button_social", "ButtonSocialInfo");
?>