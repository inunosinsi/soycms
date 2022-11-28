<?php
/*
 */
class CustomfieldReplacementStringInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=customfield_replacement_string") . '">カスタムフィールド置換文字列プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "customfield_replacement_string", "CustomfieldReplacementStringInfo");
