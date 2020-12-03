<?php
class GenerateBarcodeItemJanCodeItem extends SOYShopItemBase{

	//詳細ページを開いた時、商品詳細で設定したJANコードを元にバーコードを生成
	function executeOnDetailPage($itemId){
		SOY2::import("module.plugins.generate_barcode_item_jan_code.util.GenerateJancodeUtil");
		$jancode = GenerateJancodeUtil::getJancode($itemId);
		if(!strlen($jancode)) return;

		$dir = GenerateJancodeUtil::getJancodeDirectory(soyshop_get_item_object($itemId)->getCode());
		$jpgFile = $dir . $jancode . ".jpg";

		if(!file_exists($jpgFile)){
			require_once(dirname(dirname(__FILE__)) . "/generate_barcode_tracking_number/vendor/autoload.php");
			$generator = new Picqer\Barcode\BarcodeGeneratorJPG();
			try{
				file_put_contents($jpgFile, $generator->getBarcode($jancode, $generator::TYPE_EAN_13));
			}catch(Exception $e){
				//
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.item", "generate_barcode_item_jan_code", "GenerateBarcodeItemJanCodeItem");
