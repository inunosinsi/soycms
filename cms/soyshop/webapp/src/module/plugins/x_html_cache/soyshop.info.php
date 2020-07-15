<?php
/*
 */
class HTMLCacheInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=x_html_cache").'">HTMLキャッシュプラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "x_html_cache", "HTMLCacheInfo");
