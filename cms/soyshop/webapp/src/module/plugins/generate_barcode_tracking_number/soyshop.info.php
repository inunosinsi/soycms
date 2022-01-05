<?php
/*
 */
class GenerateBarcodeTrackingNumberInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=generate_barcode_tracking_number") . '">注文番号用バーコード生成</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "generate_barcode_tracking_number", "GenerateBarcodeTrackingNumberInfo");
