<?php
/*
 */
class ProsperityReportInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=prosperity_report").'">繁盛レポートプラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "prosperity_report", "ProsperityReportInfo");
?>