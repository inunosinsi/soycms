<?php
/*
 */
class LazyLoadInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=x_lazy_load").'">LazyLoadプラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "x_lazy_load", "LazyLoadInfo");
