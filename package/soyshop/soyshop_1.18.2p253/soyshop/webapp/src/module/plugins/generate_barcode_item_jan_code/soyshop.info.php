<?php
/*
 */
class GenerateBarcodeItemJanCodeInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=generate_barcode_item_jan_code") . '">商品JANコード用バーコード生成</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "generate_barcode_item_jan_code", "GenerateBarcodeItemJanCodeInfo");
