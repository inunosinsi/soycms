<?php
/*
 */
class FixedFormModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=fixed_form_module") . '">商品毎パーツモジュール選択読み込みプラグインの使用方法</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "fixed_form_module", "FixedFormModuleInfo");
