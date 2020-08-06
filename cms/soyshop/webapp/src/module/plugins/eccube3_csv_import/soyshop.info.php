<?php

class ECCUBE3CSVImportInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=eccube3_csv_import").'">EC CUBE3 CSVインポートプラグイン</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info","eccube3_csv_import","ECCUBE3CSVImportInfo");
