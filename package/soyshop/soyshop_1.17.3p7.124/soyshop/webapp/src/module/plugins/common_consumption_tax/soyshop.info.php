<?php
/*
 */
class CommonConsumptionTaxInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_consumption_tax").'">消費税別表示設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info","common_consumption_tax","CommonConsumptionTaxInfo");
?>
