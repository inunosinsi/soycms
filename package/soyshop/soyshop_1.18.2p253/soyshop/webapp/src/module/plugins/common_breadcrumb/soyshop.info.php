<?php
/*
 */
class CommonBreadcrumbInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=common_breadcrumb") . '">テンプレートへの記述例</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_breadcrumb", "CommonBreadcrumbInfo");
?>