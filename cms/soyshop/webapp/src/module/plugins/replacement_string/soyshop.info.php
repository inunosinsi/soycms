<?php
/*
 */
class ReplacementStringInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=replacement_string") . '">置換文字列生成プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "replacement_string", "ReplacementStringInfo");
