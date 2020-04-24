<?php
/*
 */
class SQLite2MySQLInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=sqlite2mysql").'">SQLite→MySQL移行プラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "sqlite2mysql", "SQLite2MySQLInfo");
