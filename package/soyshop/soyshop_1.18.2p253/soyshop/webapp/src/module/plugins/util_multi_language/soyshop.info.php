<?php
/*
 */
class UtilMultiLanguageInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=util_multi_language").'">多言語サイト設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","util_multi_language","UtilMultiLanguageInfo");
