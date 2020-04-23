<?php
class CommonMailbuilderInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_mailbuilder") . '">メールビルダーの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_mailbuilder", "CommonMailbuilderInfo");
