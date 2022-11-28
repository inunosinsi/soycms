<?php
/*
 */
class AutoCompletionInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=auto_completion_item_name").'">商品名検索入力補完プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "auto_completion_item_name", "AutoCompletionInfo");
