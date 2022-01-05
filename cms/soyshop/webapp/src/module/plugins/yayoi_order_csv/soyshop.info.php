<?php
/*
 */
class YayoiOrderCSVInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=yayoi_order_csv").'">弥生会計のCSV出力</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","yayoi_order_csv","YayoiOrderCSVInfo");
?>
